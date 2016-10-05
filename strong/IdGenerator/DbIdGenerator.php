<?php
namespace strong\IdGenerator;

use Yii;
use strong\helpers\FileHelper;
use strong\helpers\JsonHelper;
use yii\base\NotSupportedException;
use yii\db\Query;
use yii\db\Expression;
use yii\di\Instance;
use yii\db\Connection;

class DbIdGenerator extends IdGenerator
{
    /**
     * 使用的DB
     */
    public $db;

    /**
     * 表名
     */
    public $tableName;

    /**
     * redis队列使用的KEY
     */
    public $redisKey;

    /**
     * 使用的redis
     */
    public $redis;

    /**
     * 间隔
     */
    public $dbInterval = 100;

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, \yii\db\Connection::className());
        $this->redis = empty($this->redis) ? $this->redis : Instance::ensure($this->redis, \yii\redis\Connection::className());
    }

    public function getId()
    {
        return empty($this->redis) ? $this->getIdByDb() : $this->getIdByRedis();
    }

    public function getIdByDb()
    {
        try{
            $this->db->createCommand()->insert(
                $this->tableName,
                ['time' => time()]
            )->execute();
            return $this->db->getLastInsertID();
        }catch(\Exception $e){
            return null;
        };
    }

    public function getDbInterval()
    {
        return $this->dbInterval;
    }

    public function getIdByRedis()
    {

    }
}
