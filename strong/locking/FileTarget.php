<?php
namespace strong\locking;

use yii;
use strong\helpers\FileHelper;

class FileTarget extends Target
{
    /**
     * lock文件存放的目录
     */
	public $lockPath = '@runtime/lock';

    /**
     * lock文件的后缀名
     */
    public $lockFileSuffix = '.lock';

    /**
     * lock目录的权限
     */
    public $dirMode = 0775;

    /**
     * lock文件的权限;
     */
    public $fileMode = 0777;

    /**
     * LOCK文件的文件句柄
     */
    protected $_lockResource;

	public function init()
    {
        parent::init();
        $this->lockPath = Yii::getAlias($this->lockPath);
        is_dir($this->lockPath) OR FileHelper::createDirectory($this->lockPath  , $this->dirMode, true);
    }

    /**
     * 接触锁定, 释放占用的资源
     */
    public function unlock()
    {
    	if(!empty($this->_lockResource)){
            $this->isLock AND @flock($this->_lockResource, LOCK_UN);
            @fclose($this->_lockResource);
        }

        $this->_isLock = false;
        $this->_lockResource = null;

        return true;
    }

    /**
     * 尝试获取资源
     * @param  string  $processName 进程的名字
     * @param  string  $lockInfo    lock的信息
     * @param  boolean $wait        在无法获取锁的情况是否等待其他进程释放锁
     * @return boolean
     */
    protected function applyLock($processName, $lockInfo, $wait = false)
    {
        $lockFile = $this->lockPath . DIRECTORY_SEPARATOR . $processName . $this->lockFileSuffix;
        if(false !== ($fileResource = fopen($lockFile, 'w'))){
            if(flock($fileResource, ($wait ? LOCK_EX : (LOCK_EX | LOCK_NB)))){
                if(0 < fwrite($fileResource, $lockInfo)){
                    null === $this->fileMode OR @chmod($lockFile, $this->fileMode);
                    $this->_lockResource = $fileResource;
                    return true;
                }
            }else{
                fclose($fileResource);
            }
        }

        return false;
    }
}