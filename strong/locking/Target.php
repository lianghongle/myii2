<?php
namespace strong\locking;

use yii;

abstract class Target extends \yii\base\Object
{
    /**
     * 最大允许的实例个数;
     */
	protected $_processAmount = 1;

    /**
     * 操作的代码
     */
	protected $_operationCode;

    /**
     * 操作
     */
    protected $_operation;

    /**
     * 标识实例拿到了锁的;
     */
	protected $_isLock = false;

	public function lock($wait = false)
    {
        $lockInfo = $this->lockInfo();

        if(!$this->_isLock){
            for($i = 1; $i <= $this->_processAmount; $i++){
                $processName = $this->_operationCode . '_' . $i;
                if($this->applyLock($processName, $lockInfo, $wait)){
                    $this->_isLock = true;
                    break;
                }
            }
        }

        return $this->_isLock;
    }

    abstract public function unlock();

    abstract protected function applyLock($processName, $lockInfo, $wait = false);

    public function setOperationCode($operationCode)
    {
        $this->_operationCode = $operationCode;
    }

    public function setOperation($operation)
    {
        $this->_operation = $operation;
    }

    public function setProcessAmount($processAmount)
    {
        $this->_processAmount = $processAmount;
    }

    public function lockInfo()
    {
        return sprintf(
            'pid:%s, operationCode:%s, operation:%s, processAmount:%s, time:%s',
            getmypid(),
            $this->_operationCode,
            $this->_operation,
            $this->_processAmount,
            time()
        );
    }

    public function __destruct()
    {
        $this->unlock();
    }
}