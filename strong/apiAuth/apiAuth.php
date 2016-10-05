<?php
namespace strong\apiAuth;

use yii;
use strong\helpers\FileHelper;
use yii\di\Instance;
use strong\redis\HashConnection;
use yii\caching\Cache;
use strong\helpers\TimeHelper;

class apiAuth extends \yii\base\Object
{
    public $gameid;

    public $tokenKeyPrefix;

    public $tokenExpire;

    public $gameKey;

    public $redis;

    public $cache;

    public $userTokenKeyPrefix;

    public $recordUserTokenKeyExpire;

    public $logFile;

    public $dirMode = 0775;

    public $fileMode;

    public function init()
    {
        parent::init();

        $this->redis = Instance::ensure($this->redis, HashConnection::className());
        $this->cache = Instance::ensure($this->cache, Cache::className());

        if(null !== $this->logFile){
            $this->logFile = Yii::getAlias($this->logFile);
            is_dir(dirname($this->logFile)) OR FileHelper::createDirectory($path, $this->dirMode);
        }
    }

    public function createToken($uid)
    {
        //首先删除原先的TOKEN
        $oldTokenKey = $this->getUserTokenKey($uid);
        empty($oldTokenKey) OR $this->redis->executeCommand('DEL', [$oldTokenKey]);

        //随机生成一个KEY
        $tokenKey = $this->generateKey(true);

        $redisKey = $this->buildKey($tokenKey);

        //记录最新的
        $this->recordUserTokenKey($uid, $redisKey);

        //设置最新的TOKEN
        $tokenData = array(
            'game_id'      => $this->gameid,
            'token_secret' => $this->generateKey(),
            'token_type'   => 'access',
            'authorized'   => 1,
            'updated'      => time(),
            'player_id'    => $uid
        );

        $redis = $this->redis->getConnection($redisKey);

        $args = [$redisKey];
        foreach ($tokenData as $key => $value) {$args[] = $key;  $args[] = $value;}
        $redis->executeCommand('MULTI');
        $redis->executeCommand('HMSET', $args);
        $redis->executeCommand('EXPIRE', [$redisKey, $this->tokenExpire]);
        $result = $redis->executeCommand('EXEC');

        if(true !== $result[0] && '1' !== $result[1]){
            return false;
        }

        //记录日志
        if(null !== $this->logFile){
            $string = sprintf(
                'time:%s uid:%s old:%s new:%s redis:%s' . PHP_EOL,
                TimeHelper::date('Y-d-m H:i:s.u'),
                $uid,
                $oldTokenKey,
                $redisKey,
                $redis->hostname . ':' . $redis->port
            );
            FileHelper::putContents($this->logFile, $string, true, true, $this->fileMode);
        }

        $tokenData['token_key'] = $tokenKey;
        return $tokenData;
    }

    public function getUserToken($uid)
    {
        $tokenKey = $this->getUserTokenKey($uid);
        return $this->redis->executeCommand('HVALS', [$tokenKey]);
    }

    public function getUserTokenKey($uid)
    {
        return $this->cache->get($this->userTokenKeyPrefix . $uid);
    }

    public function recordUserTokenKey($uid, $tokenKey)
    {
        return $this->cache->set($this->userTokenKeyPrefix . $uid, $tokenKey);
    }

    public function buildKey($tokenKey)
    {
        return $this->tokenKeyPrefix . $tokenKey;
    }

    protected function generateKey( $unique = false ) {
        $key = md5(uniqid(rand(), true));
        if ($unique) {
            list($usec, $sec) = explode(' ',microtime());
            $key .= dechex($usec) . dechex($sec);
        }
        return $key;
    }
}