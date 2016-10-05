<?php
namespace strong\maps;

use yii;

class MapsEntrance extends \yii\base\Object {

	public $engines;//配置
	public $defaulEngines;
	public $_engines;

	public function init()
	{
		parent::init();
	}

	public function engine($name = null){
		$name = null===$name ? $this->defaulEngines : $name;
		if(!isset($this->_engines[$name])){
			$this->_engines[$name] = Yii::createObject($this->engines[$name]);
		}

		return $this->_engines[$name];
	}
}