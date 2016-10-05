<?php
return [
//	'bootstrap' => ['debug'],
//    'modules' => [
//        'debug' => [
//            'class' => 'yii\debug\Module',
//            'controllerNamespace' => 'strong\debug\controllers',
//            'enableDebugLogs' => true,
//
//            //如果是访问虚拟机，要用和虚拟机一个网段的那个ip
//            'allowedIPs' => ['172.16.158.1', '127.0.0.1', '::1'],
//            //'allowedIPs' => ['*'],
//
//            'panels' => [
//                'elasticsearch' => [
//                    'class' => 'yii\\elasticsearch\\DebugPanel',
//                ],
//                'mongodb' => [
//                    'class' => 'yii\\mongodb\\debug\\MongoDbPanel',
//                ],
//            	'other' => ['class' => 'strong\debug\panels\OtherPanel'],
//            ],
//        ],
//    ],

	'class' => 'yii\debug\Module',
	'controllerNamespace' => 'strong\debug\controllers',
	'enableDebugLogs' => true,

	//如果是访问虚拟机，要用和虚拟机一个网段的那个ip
	'allowedIPs' => ['172.16.158.1', '127.0.0.1', '::1'],
	//'allowedIPs' => ['*'],

	'panels' => [
		'elasticsearch' => [
			'class' => 'yii\\elasticsearch\\DebugPanel',
		],
//		'mongodb' => [
//			'class' => 'yii\\mongodb\\debug\\MongoDbPanel',
//		],
		'other' => ['class' => 'strong\debug\panels\OtherPanel'],
	],
];