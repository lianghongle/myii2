<?php
namespace console\controllers;

use yii\console\Controller;

class TestController extends Controller
{
	public $message;

	public function options($actionID)
	{
		return ['message'];
	}

	public function optionAliases()
	{
		return ['m' => 'message'];
	}

	public function actionIndex()
	{
		echo __METHOD__.PHP_EOL;
		echo $this->message . "\n";
	}
}