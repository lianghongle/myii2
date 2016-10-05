<?php
namespace strong\db;

use yii;
use yii\caching\Cache;

class Connection extends \yii\db\Connection
{
    /**
     * 是否开启重连, 可以是一个boolean或者是一个函数;
     * @var boolean
     */
    public $enableReconnection = false;

    /**
     * 重连尝试的间隔时间, 单位:微秒(一微秒等于百万分之一秒);
     * @var integer
     */
    public $reconnectionInterval = 1;

    /**
     * 重连的错误号;
     * @var array
     */
    public $reconnectionpdoErrorCodes = [];

    /**
     * 重连事件名;
     */
    const EVENT_RECONNECTION = 'reconnection';

    public function canReconnection()
    {
        if(is_bool($this->enableReconnection)){
            return $this->enableReconnection;
        }else{
            return call_user_func($this->enableReconnection, $this);
        }
    }

    protected function createPdoInstance()
    {
        if($this->canReconnection()){
            try {
            	return parent::createPdoInstance();
	        } catch (\PDOException $e) {
                Yii::warning("Connection ({$this->dsn}) failed: " . $e->getMessage(), __METHOD__);
	        	$this->trigger(static::EVENT_RECONNECTION);
	        	$this->reconnectionInterval > 0 AND usleep($this->reconnectionInterval);
	        	return $this->createPdoInstance();
	        }
        }else{
        	return parent::createPdoInstance();
        }
    }


    public function createCommand($sql = null, $params = [])
    {
        $command = new Command([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }
}
