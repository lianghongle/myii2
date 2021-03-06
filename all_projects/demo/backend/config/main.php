<?php
$params = [];

$main_conf =  [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
	    'request' => [
		    'csrfParam' => '_csrf-backend',
		    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
		    'enableCsrfValidation' => false,
		    'cookieValidationKey' => 'lhl',

		    //根据请求头类型,接受参数做相应处理
		    'parsers' => [
			    'application/json' => 'yii/web/JsonParser',
			    'text/json' => 'yii/web/JsonParser',
		    ]
	    ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
	            'name' => '_identity-backend',
	            'httpOnly' => true
            ],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
//    'params' => $params,
    'params' => [],
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
