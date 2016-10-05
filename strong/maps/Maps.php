<?php
namespace strong\maps;

abstract class Maps extends \yii\base\Object
{
	protected $PROXY = '';
	protected $KEY = '';
	protected $URI = '';
	protected $TIME_OUT = 25; // 所有请求，在三秒内没有响应数据，就报错
	protected $LANG = '';
	public $url_translate='translate';
	public $url_request= 'request';

	const EARTH_RADIUS = 6378.14;//地球半径

	public function init()
	{
		parent::init();
	}

	public function setLanguage($lang){
		$this->LANG = $lang;
		return $this;
	}

	protected function getKey($url){
		return 'Map:'.md5(preg_replace_callback('#(\d+\.\d+)#',function($m){return round($m[1],5);},$url)); // 四舍五入保留5位坐标用于缓存
	}

	public function store($key,$value,$ttl){
// 		$cache = new BaseModule;
// 		$value = json_encode($value);
// 		return $cache->redisCache()->set($key,$value,$ttl);
	}

	public function cached($key){
// 		$cache = new BaseModule;
// 		$value = $cache->redisCache()->get($key);
// 		if($value) return json_decode($value,1);
// 		else false;
	}

	/**
	 * 地图请求信息存redis
	 * @author Felix hu
	 * @param 地图引擎 $engine
	 * @param 来源页面 $pagefrom
	 * @param 请求类型 $request_type，分转译和请求
	 * $redis_tb 统计的表名，格式maps_google_request
	 * $redis_k  键名 ，格式 20150403_1_translate,20150403_1_request
	 */
	public function mapsRequest($engine,$pagefrom,$request_type){
// 		$cache = new BaseModule;
// 		$redis_tb='maps_'.$engine.'_total';
// 		$date=date('Ymd',time());
// 		$request_type=trim($request_type);
// 		//$redis_k=$date.'_'.$pagefrom.'_'.$request_type;
// 		$redis_k=$date.'_'.$request_type;

// 		return  $cache->redisCache()->hincrby($redis_tb,$redis_k,1);
	}

	protected function request($url, $params='', $method='GET'){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->TIME_OUT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if($this->PROXY){
			// 使用自由门代理
			list($host,$port) = explode(':', $this->PROXY);
			curl_setopt($ch, CURLOPT_PROXY, $host);
			curl_setopt($ch, CURLOPT_PROXYPORT, $port);
		}

		if($params) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		# use https, for google
		if(strpos($url,'https://')===0){
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		#
		$ip = $this->getIp();
		$url = str_replace($ip[0], $ip[1], $url);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Host: {$ip[0]}"]);

		$result = array('head'=>'','body'=>'');
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $data) use ($ch, & $result){
				$result['head'] .= $data;
				return strlen($data);
		});

		$response = curl_exec($ch);
		if($response === false){
			throw new \Exception(curl_error($ch));
		}
		curl_close($ch);

		$result['body'] = $response;

		return $result;
	}

	/**
	* 附近的信息
	* 请求：location坐标， radius半径距离， keyword关键字,  pagetoken分页信息(包括条数，当前页，访问Key)
	* 响应：
	*      Google： id,        geometry.location,   name,  vicinity,
	*      Baidu:   street_id, location,            name,  address,
	*      Tencent: id,        location,            title, address,
	*      $location 为数组，第一个为数字下标，第二个为经纬度来源（Google、Baidu、Tencent等）
	*      $page 来源页面
	*/
	abstract function nearby(array $location, $page,$radius, $keyword='', $pagetoken='');

	/**
	* 获得域名和IP  ['domain', 'ip']
	*/
	abstract protected function getIp();

	/**
	 * 计算二点之间的距离, 返回的单位为千米
	 * 经纬度可以这样传： ['lat'=>22.22, 'lng'=>120.20],也可以这样传 22.22,120.20,纬度,经度
	 */
	static function distance($location1, $location2) {
		if (is_array ( $location1 ) && is_array ( $location2 )) {
			$longitude1 = $location1 ['lng'];
			$latitude1 = $location1 ['lat'];
			$longitude2 = $location2 ['lng'];
			$latitude2 = $location2 ['lat'];
		} else {
			list ( $latitude1, $longitude1 ) = explode ( ',', $location1 );
			list ( $latitude2, $longitude2 ) = explode ( ',', $latitude2 );
		}
		$rad_latitude1 = deg2rad ( $latitude1 );
		$rad_latitude2 = deg2rad ( $latitude2 );
		$a = $rad_latitude1 - $rad_latitude2;
		$b = deg2rad ( $longitude1 ) - deg2rad ( $longitude2 );
		return 2 * asin ( sqrt ( pow ( sin ( $a / 2 ), 2 ) + cos ( $rad_latitude1 ) * cos ( $rad_latitude2 ) * pow ( sin ( $b / 2 ), 2 ) ) ) * self::EARTH_RADIUS;
	}
}