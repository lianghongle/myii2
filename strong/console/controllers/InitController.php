<?php

namespace strong\console\controllers;

use Yii;
use strong\helpers\Console;
use strong\helpers\FileHelper;

/**
 * 初始化项目.
 */
class InitController extends \yii\console\Controller
{
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * 初始化项目.
     */
    public function actionRuntime()
    {

    }
}
