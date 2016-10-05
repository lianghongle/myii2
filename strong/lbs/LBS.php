<?php
namespace strong\lbs;

use strong\helpers\ArrayHelper;
use strong\helpers\JsonHelper;
use strong\helpers\MathHelper;
use yii\caching\Cache;
use yii\di\Instance;
use yii\base\UserException;
use strong\queue\Queue;

abstract class LBS extends \yii\base\Object
{
    /**
     * 使用的缓存组件
     */
    public $cache;

    /**
     * 用户经纬度的缓存KEY
     */
    public $userCacheKeyPrefix = 'radar_lbs_user_';

    /**
     * 用户经纬度的缓存过期时间
     */
    public $userCacheKeyExpire = 604800;

    /**
     * 上传间隔缓存的key
     */
    public $uploadIntervalCacheKeyPrefix;

    /**
     * 两次上传间隔的时间
     */
    public $uploadIntervalExpire = 600;

    /**
     * 是否开启上传间隔;
     */
    public $enableUploadInterval = true;

    /**
     * 是否开启用户信息缓存;
     */
    public $enableUserCache = true;

    /**
     * 使用那个队列, 该队列必须单独使用
     */
    public $queue;

    /**
     * 批量上传的最大值
     */
    public $batchConsumeQueue = 200;

    public function init()
    {
        parent::init();

        //$this->queue = Instance::ensure($this->queue, Queue::className());
        $this->cache = null !== $this->cache ? Instance::ensure($this->cache, Cache::className()) : null;
        $this->enableUserCache = $this->cache instanceof Cache && $this->enableUserCache;
        $this->enableUploadInterval = $this->cache instanceof Cache && $this->enableUploadInterval;
    }

    /**
     * 获取用户的信息
     */
    public function getUsers(array $uids)
    {
        $list = ArrayHelper::index($this->getUserFromCache($uids), 'uid');

        //剩下的从DB里面去获取, 并且设置到缓存;
        $notInCache = array_keys(array_diff_key(array_flip($uids), $list));
        if(!empty($notInCache)){
            $notInCacheList = $this->getUserItems($notInCache);
            if(!empty($notInCacheList)){$this->setUserCache($notInCacheList);}

            $list = array_merge($list, $notInCacheList);
        }

        return ArrayHelper::index($list, 'uid');
    }

    /**
     * 批量上传
     */
    public function batchUpload(array $list)
    {
        if(!$this->uploadItems($list)){return false;}

        $this->deleteUserCache(array_map(function($user){
            return $user['uid'];
        }, $list));

        return true;
    }

    /**
     * 雷达搜索
     *
     * Yii::$app->lbs->search(
     *       [
     *           'updated' => ['between' => [0, 1000000000000000000]],   //一定要传范围, 小值在前, 大值在后
     *           'beauty_rating' => ['between' => [0, 100]],             //一定要传范围, 小值在前, 大值在后
     *           'gender' => ['eq' => 1],
     *           'type' => ['eq' => 0],
     *           'quality' => ['eq' => 1],
     *           'uid' => [
     *               'in' => [23778, 23779],
     *               'notin' => []
     *           ],
     *           'radius' => 1000000,
     *       ],
     *       [
     *           'updated' => 'desc',
     *           'radius' => 'asc'
     *       ],
     *       1,
     *       50
     * );
     *
     * @param  string  $location    所在经纬度
     * @param  array   $where       查询条件
     * @param  array   $sort        排序方式
     * @param  integer $page        页码
     * @param  integer $pageSize    分页大小
     * @return array
     *
     *  [
     *   {
     *       "location": "113.943213,22.549377",
     *       "updated": 1430908717,
     *       "type": 0,
     *       "gender": 1,
     *       "quality": 1,
     *       "beauty_rating": 50,
     *       "uid": "2623246",
     *       "distance": 39497
     *   }
     *   ]
     */
    public function search($user, array $where, array $sort, $page = 1, $pageSize = 50)
    {
        //进去队列
        $this->upload($user);

        $page = intval($page);
        $page = 0 < $page ? $page : 1;
        $offset = ($page - 1) * $pageSize;

        $list = $this->searchItems($user['location']['location'], $where, $sort, $offset, $pageSize);
        $list = array_map(function($item) use ($location){
            $item['distance'] = $this->getDistance($location, $item['location']);
            return $item;
        }, $list);

        return $list;
    }

    /**
     * 上传用户的地理位置信息
     * @param  array $user [description]
     *   [
     *       "location": [
     *           "location" => "113.943213,22.549377",
     *           "city" => "深圳市"
     *        ],
     *       "updated" => 1430908717,
     *       "type" => 0,
     *       "gender" => 1,
     *       "quality" => 1,
     *       "beauty_rating" => 50,
     *       "uid" => "2623246",
     *   ]
     * @return [type]       [description]
     */
    public function upload($user)
    {

    }

    /**
     * 删除用户的信息
     */
    public function delete(array $uids)
    {
        if(!$this->deleteItems($uids)){return false;}

        $this->deleteUserCache($uids);

        return true;
    }

    public function pushQueue($queue)
    {
        $this->queue->push($queue);
    }

    public function executeQueues()
    {
        $queues = $this->queue->push($this->batchConsumeQueue);

        $list = [];
        foreach ($queues as $queue) {

        }

        if(!empty($list)){
            if(!$this->batchUpload($list)){
                throw new UserException('Upload failed, list:' . JsonHelper::encode($list));
            }
        }

        return count($queues);
    }

    /**
     * 获取用户的距离
     * @param  $cuid 当前用户
     * @param  $uids 需要算出和当前用户距离的用户,  例如  [11111, 22222]
     * @return ['用户id' => '距离'], 例如  [11111 => 100, 22222 => -1], -1: 表示没有距离
     */
    public function getUserDistance($cuid, array $uids)
    {
        $users = $this->getUsers(array_merge($uids, [$cuid]));

        $distance = [];
        foreach ($uids as $key => $uid) {
            if(isset($users[$cuid], $users[$uid])){
                $distance[$uid] = $this->getDistance($users[$cuid]['location'], $users[$uid]['location']);
            }else{
                $distance[$uid] = -1;
            }
        }

        return $distance;
    }

    protected function getUserCacheKeys($uids)
    {
        return array_map(function($uid){
            return $this->userCacheKeyPrefix . $uid;
        }, $uids);
    }

    protected function setUserCache($list)
    {
        if(!$this->enableUserCache){return true;}

        $data = [];
        foreach ($list as $user) {
            $cacheKey = $this->getUserCacheKeys([$user['uid']]);
            $data[current($cacheKey)] = $user;
        }
        return 0 == count($this->cache->mset($data, $this->userCacheKeyExpire));
    }

    protected function getUserFromCache($uids)
    {
        if(!$this->enableUserCache){return [];}

        $cacheKeys = $this->getUserCacheKeys($uids);
        $list = $this->cache->mget($cacheKeys);
        return array_filter($list, function($user){
            return false !== $user;
        });
    }

    protected function deleteUserCache($uids)
    {
        if(!$this->enableUserCache){return true;}

        $cacheKeys = $this->getUserCacheKeys($uids);
        foreach ($cacheKeys as $cacheKey) {
            $this->cache->delete($cacheKey);
        }
        return true;
    }

    abstract protected function getUserItems(array $uids);

    abstract protected function uploadItems(array $list);

    abstract protected function searchItems($location, array $where, array $sort, $offset, $limit);

    abstract protected function deleteItems($uid);

    public function isLocation($location)
    {
    	return is_array($this->analyseLocation($location));
    }

    public function analyseLocation($location)
    {
    	$locationArray = explode(',', $location);
	    if (
	        2 != count($locationArray)
	        || !is_numeric($locationArray[0]) || !is_numeric($locationArray[1])
	        || (0 == $locationArray[0] && 0 == $locationArray[1])
	        || abs($locationArray[0]) >= 180 || abs($locationArray[1]) >= 90
	    ) {
	        return false;
	    } else {
	        return array('lat'=> $locationArray[1],'lng'=> $locationArray[0]);
	    }
    }

    public function getDistance($locationA, $locationB)
    {
    	$locationA = $this->analyseLocation($locationA);
    	$locationB = $this->analyseLocation($locationB);

    	if(empty($locationB) || empty($locationA)){
    		return -1;
    	}

    	$lngA = $locationA['lng'];
    	$latA = $locationA['lat'];

    	$lngB = $locationB['lng'];
    	$latB = $locationB['lat'];

        $earthRadius = 6367000;
        $latA = ($latA * pi() ) / 180;
        $lngA = ($lngA * pi() ) / 180;
        $latB = ($latB * pi() ) / 180;
        $lngB = ($lngB * pi() ) / 180;
        $calcLongitude = $lngB - $lngA;
        $calcLatitude = $latB - $latA;
        $stepOne = pow(sin($calcLatitude / 2), 2)
        		+ cos($latA) * cos($latB) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return intval($calculatedDistance);
    }

    const LNG_MIN = 0;
    const LNG_MAX = 180;
    const LAT_MIN = 0;
    const LAT_MAX = 90;

    /**
     * 坐标精度（小数点位数）
     */
    const COORDINATE_PRECISION = 6;

    /**
     * 随机生成一个坐标
     *
     * @param int $minLng       最小经度
     * @param int $maxLng       最大经度
     * @param int $minLat       最小维度
     * @param int $maxLat       最大维度
     * @return array
     */
    public static function randomCoordinate($minLng = 0, $maxLng = 0, $minLat = 0, $maxLat = 0)
    {
        $lngUnsigned = $latUnsigned = false;

        if(empty($minLng)){
            $minLng = static::LNG_MIN;
        }else{
            if($minLng > 0){
                $lngUnsigned = false;
            }
        }
        $maxLng = !empty($maxLng) ? $maxLng : static::LNG_MAX;

        if(empty($minLat)){
            $minLat = static::LAT_MIN;
        }else{
            if($minLat > 0){
                $latUnsigned = false;
            }
        }
        $maxLat = !empty($maxLat) ? $maxLat : static::LAT_MAX;

        if($lngUnsigned){
            $lng = MathHelper::randomFloat($minLng, $maxLng, static::COORDINATE_PRECISION);
        }else{
            if(rand(0, 1)){
                $lng = 0 - MathHelper::randomFloat($minLng, $maxLng, static::COORDINATE_PRECISION);
            }else{
                $lng = MathHelper::randomFloat($minLng, $maxLng, static::COORDINATE_PRECISION);
            }
        }

        if($latUnsigned){
            $lat = MathHelper::randomFloat($minLat, $maxLat, static::COORDINATE_PRECISION);
        }else{
            if(rand(0, 1)){
                $lat = 0 - MathHelper::randomFloat($minLat, $maxLat, static::COORDINATE_PRECISION);
            }else{
                $lat = MathHelper::randomFloat($minLat, $maxLat, static::COORDINATE_PRECISION);
            }
        }

        return [
            'lng' => $lng,
            'lat' => $lat
        ];
    }
}