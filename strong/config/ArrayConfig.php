<?php
namespace strong\config;

class ArrayConfig extends Config
{
	protected $_config;

	protected $_canInitConfig = true;

    public function getValue($key = null)
    {
    	if(null === $key){
    		return $this->_config;
    	}else{
    		return isset($this->_config[$key]) ? $this->_config[$key] : null;
    	}
    }

    public function __set($name, $value)
    {
    	//第一次能够设置初始化配置
    	if(in_array($name, ['config']) && $this->_canInitConfig){
    		$this->{"_{$name}"} = $value;
            $this->_canInitConfig = false;
    	}else{
    		parent::__set($name, $value);
    	}
    }
}