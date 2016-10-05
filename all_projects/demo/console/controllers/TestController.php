<?php
namespace console\controllers;

use yii\console\Controller;
use yii\helpers\Console;

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

//		Controller::EXIT_CODE_NORMAL 值为 0;
//		Controller::EXIT_CODE_ERROR 值为 1.

		//{{ Yii 支持格式化输出， 如果终端运行命令不支持的话则会自动退化为非格式化输出。
		$this->stdout("Hello?\n", Console::FG_GREEN);

		$name = $this->ansiFormat('Alex', Console::FG_YELLOW);
		echo "Hello, my name is $name.";
		//}}


		echo __METHOD__.PHP_EOL;
		echo $this->message . "\n";
	}

	// 命令 "yii example/create test" 会调用 "actionCreate('test')"
//	public function actionCreate($name) { ... }

	// 命令 "yii example/index city" 会调用 "actionIndex('city', 'name')"
	// 命令 "yii example/index city id" 会调用 "actionIndex('city', 'id')"
//	public function actionIndex($category, $order = 'name') { ... }

	// 命令 "yii example/add test" 会调用 "actionAdd(['test'])"
	// 命令 "yii example/add test1,test2" 会调用 "actionAdd(['test1', 'test2'])"
//	public function actionAdd(array $name) { ... }
}