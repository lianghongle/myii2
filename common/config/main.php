<?php

$config = [
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'charset' => 'UTF-8',

	//国际化使用
//	'language' => 'zh_cn',
//	'sourceLanguage' => 'zh_cn',

	'timeZone' => 'PRC',
];

if((YII_ENV_LOCAL || YII_ENV_DEV ) || \strong\helpers\CheckHelper::sapi('cli')){
	$config['bootstrap'][] = 'gii';
	$config['modules']['gii'] = require 'gii.php';
}

if(YII_DEBUG){
	$config['bootstrap'][] = 'debug';
	$config['modules']['debug'] = require 'debug.php';
}

return $config;
