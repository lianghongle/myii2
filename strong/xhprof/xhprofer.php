<?php

namespace strong\xhprof;

use yii;
use strong\helpers\FileHelper;
use strong\helpers\StringHelper;

class xhprofer extends \yii\base\Object
{
	protected $_isEnable = false;
	protected $_source;
	protected $_run;

	public $dataPath = '@runtime/xhprof';
	public $dirMode = 0775;
	public $displayUrl = '';
	public $enable = null;

	public function init()
    {
    	parent::init();
        $this->dataPath = Yii::getAlias($this->dataPath);
        is_dir($this->dataPath) OR FileHelper::createDirectory($this->dataPath, $this->dirMode, true);
        $this->enable = extension_loaded('xhprof') && in_array($this->enable, array(true, null), true);
    }

	public function enable()
	{
		if(!$this->enable){return false;}

		xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
		$this->_isEnable = true;

		$this->_source = sprintf(
        	'%s-%s',
        	Yii::$app->id,
        	StringHelper::uniqid(1)
        );

		return true;
		//register_shutdown_function([$this, 'saveRun']);
	}

	public function saveRun()
	{
		if(!$this->enable){return false;}

    	include(Yii::getAlias('@strong/xhprof/xhprof_lib/utils/xhprof_lib.php'));
    	include(Yii::getAlias('@strong/xhprof/xhprof_lib/utils/xhprof_runs.php'));
		$xhprofData = xhprof_disable();

		$xhprofRuns = new \XHProfRuns_Default($this->dataPath);
    	$this->_run = $xhprofRuns->save_run($xhprofData, $this->_source);
	}

	public function getIsEnable()
	{
		return $this->_isEnable;
	}

	public function getRun()
	{
		return $this->_run;
	}

	public function getSource()
	{
		return $this->_source;
	}
}