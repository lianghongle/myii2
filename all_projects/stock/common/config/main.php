<?php

//项目公用配置
$common_main_conf = [
	'components' => yii\helpers\ArrayHelper::merge(
		require (__DIR__ . '/' . YII_ENV . '/cache.php'),
//		require (__DIR__ . '/' . YII_ENV . '/redis.php'),
//		require (__DIR__ . '/' . YII_ENV . '/mongodb.php'),
//		require (__DIR__ . '/' . YII_ENV . '/elasticsearch.php'),
		require (__DIR__ . '/' . YII_ENV . '/mysql.php')
	),
];

$common_main_conf = array_merge(
	require(__DIR__ . '/../../../../common/config/main.php'),
	$common_main_conf
);

if(defined('YII_CONF_LOCAL') && YII_CONF_LOCAL){
	$common_main_conf = array_merge(
		require(__DIR__ . '/../../../../common/config/main-local.php'),
		$common_main_conf
	);
}

return $common_main_conf;

