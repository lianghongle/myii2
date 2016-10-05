<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$main_conf = [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];

$main_conf = array_merge(
	require(__DIR__ . '/../../common/config/main.php'),
	$main_conf
);

if(defined('YII_CONF_LOCAL') && YII_CONF_LOCAL){
	$main_conf = array_merge(
		require(__DIR__ . '/../../common/config/main-local.php'),
		$main_conf
	);
}

return $main_conf;
