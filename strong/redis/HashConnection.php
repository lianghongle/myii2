<?php
/**
 * 实现redis的回环hash
 */

namespace strong\redis;

use yii;

class HashConnection extends \yii\base\Object
{
	public $servers;

	public $redisConfig;

	protected $hash;

	protected $connectionPool = [];

    public function init()
    {
    	parent::init();
    	$this->initHash();
    }

    protected function initHash()
    {
    	include(__DIR__ . '/Flexihash.php');
    	$this->hash = new \Flexihash();
    	$targets = array_keys($this->servers);
    	$this->hash->addTargets($targets);
    }

    public function getConnection($key)
    {
    	$target = $this->hash->lookupList($key, 1)[0];

    	if(!isset($this->connectionPool[$target])){
    		$config = array_merge($this->redisConfig, $this->servers[$target]);
            $this->connectionPool[$target] = Yii::createObject($config);
    	}

        return $this->connectionPool[$target];
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->getConnection($params[0]), $name], $params);
    }
}