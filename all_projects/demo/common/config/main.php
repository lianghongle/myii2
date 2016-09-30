<?php

$common_main_conf = [];
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

