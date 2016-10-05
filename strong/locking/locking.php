<?php
namespace strong\locking;

use yii;
use strong\helpers\JsonHelper;
use yii\base\InvalidCallException;

//Yii::Yii::$app->lock->getInstance('test', 2)->lock();
//Yii::$app->lock->getInstance('test', 2)->unlock();

class locking extends \yii\base\Object
{
    /**
     * LOCK的实现对象的单例;
     */
	protected $_instances = [];

    /**
     * 具体实现LOCK的实现对象配置
     */
	public $target;

	/**
	 * 创建一个locking实例;
	 * @param  $operation	操作的key
	 * @param  integer $processAmount 最多允许几个实例存在;
	 * @return Object;
	 */
	public function getInstance($operation, $processAmount = 1){
        if(is_array($operation)){
        	ksort($operation);
        	$operation = JsonHelper::encode($operation);
        }

        $operationCode = md5($operation);

        if(!isset($this->_instances[$operationCode])){
            $this->_instances[$operationCode] = Yii::createObject(array_merge($this->target, [
                'processAmount' => $processAmount,
                'operationCode' => $operationCode,
                'operation' => $operation
            ]));
        }

        return $this->_instances[$operationCode];
    }
}