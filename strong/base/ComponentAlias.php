<?php

namespace strong\base;

use yii;
use yii\di\Instance;
/**
 * 组件别名类
 */
class ComponentAlias
{
	public $component;
	public $componentClass;

    public function __construct($config = [])
    {
        if (!empty($config)) {Yii::configure($this, $config);}
        $this->init();
    }

    public function init()
    {
    	$this->component = Instance::ensure($this->component, $this->componentClass);
    }

    public function __get($name)
    {
        return $this->component->$name;
    }

    public function __set($name, $value)
    {
        $this->component->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->component->$name);
    }

    public function __unset($name)
    {
        $this->component->$name = null;
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->component, $name], $params);
    }
}
