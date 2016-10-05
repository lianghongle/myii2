<?php

namespace strong\console;

use yii;

class Controller extends \yii\console\Controller
{
	protected $_locker;


	public function processAmountRules()
	{
		return [];
	}

	public function beforeAction($action)
    {
        if(parent::beforeAction($action)){
        	//如果没有lock组建不执行
        	if(Yii::$app->has('lock')){
        		return true;
        	}

        	//获取最大实例数
			$actionId = $this->action->id;
			$processAmountRules = $this->processAmountRules();
			$processAmountRules = isset($processAmountRules[$actionId]) ? $processAmountRules[$actionId] : null;

			$count = isset($processAmountRules['count']) ? $processAmountRules['count'] : 0;
			$callback = isset($processAmountRules['callback']) ? $processAmountRules['callback'] : null;

			//如果没有设置最大实例数, 或者为零, 不限制实例的个数;
			if(0 == $count){
				return true;
			}

			//检查是否能够获取执行权限
			$this->_locker = Yii::$app->lock->create([
				'controller' => static::className(),
				'action' => $this->action->id,
				'app' => Yii::$app->id
			], $count);

			if($this->_locker->lock(false)){
				return true;
			}else{
				if(null === $callback){
					$params = Yii::$app->Request->getParams();
					throw new \yii\console\Exception("命令: {$params[0]} 的实例不能超过 {$count} 个");
				}else{
					call_user_func($callback, $this);
				}
				return false;
			}
        }

        return false;
    }


	public function afterAction($action, $result)
    {
    	if(parent::afterAction($action, $result)){
    		if(empty($this->_locker)){
    			$this->_locker->unlock();
    		}
    		return true;
    	}

    	return false;
    }
}
