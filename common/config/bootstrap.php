<?php

/**
 * Whether the the application is running in testing environment
 */
defined('YII_ENV_LOCAL') || define('YII_ENV_LOCAL', YII_ENV === 'local');

//框架增强目录路径别名;
Yii::setAlias('strong', __DIR__ . '/../../strong');

//Yii::setAlias('@common', dirname(__DIR__));
//Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
//Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
//Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
