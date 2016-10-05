<?php

/*
 * GII模块只在开发环境下, 或者cli模式下开启
 */
return [
//	'bootstrap' => ['gii'],
//    'modules' => [
//        'gii' => [
//            'class' => 'yii\gii\Module',
//            'allowedIPs' => ['172.16.158.1','127.0.0.1', '::1', '192.168.0.*'], // adjust this to your needs
//            'generators' => [
//                'mongoDbModel' => [
//                    'class' => 'yii\mongodb\gii\model\Generator'
//                ]
//            ]
//        ]
//    ],

	'class' => 'yii\gii\Module',
	'allowedIPs' => ['172.16.158.1','127.0.0.1', '::1', '192.168.0.*'], // adjust this to your needs
	'generators' => [
//		'mongoDbModel' => [
//			'class' => 'yii\mongodb\gii\model\Generator'
//		]
	]
];