<?php
namespace strong\jnear;

use Yii;
use strong\helpers\TimeHelper;
use strong\helpers\FileHelper;

class Client extends \yii\base\Object
{
	public $appid;

	public $secret;

	public $jnear;

	public function init()
	{
		include(__DIR__ . '/sdk/php/JNear.php');
		$this->jnear = new \JNear([
			'appid' => $this->appid,
			'secret' => $this->secret
		]);
	}


	public function __call($name, $params)
	{
		if(method_exists($this->jnear, $name)){
			return call_user_func_array([$this->jnear, $name], $params);
		}else{
			return parent::__call($name, $params);
		}
	}
}