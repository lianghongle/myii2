<?php
namespace strong\queue;

use yii;
use strong\helpers\JsonHelper;

abstract class Queue extends \yii\base\Object
{
	/**
	 * 任务最大的长度; 0:表示暂停使用队列任务, -1:无限
	 */
	public $maxLength = -1;

	/**
	 * 队列的名字
	 */
	public $queueName;

	/**
	 * 是否自动去重
	 */
	public $deduplication = false;

	/**
	 * 记录的最大错误数;
	 */
	public $maxErrors = 10;

	/**
	 * 系列换队列的参数;
	 */
	public $serializer = ['serialize', 'unserialize'];

	/**
	 * 最大缓存的worker, 防止以后消费队列跑太久， 缓存的worker太多
	 */
	public $maxCacheWorker = 100;

	/**
	 * 记录错误数
	 */
	protected $_errors = [];

	protected $_workerObjects = [];

	/**
	 * 添加一个任务
	 * @param  string $queue 队列任务
	 * @return [type]        [description]
	 */
	public function push($queue)
	{
		if(0 == $this->maxLength){return true;}

		if(0 < $this->maxLength){
			if($this->length() >= $this->maxLength){
				$this->addError('queue length transfinite');
				return false;
			}
		}

		return $this->pushQueue(call_user_func($this->serializer[0], $queue));
	}

	public function pop($length = 1)
	{
		$queues = $this->popQueues($length);
		return array_map(function($queue){
			return empty($queue) ? $queue : call_user_func($this->serializer[1], $queue);
		}, $queues);
	}

	public function executeQueues($length = 1, $clearErrors = true)
	{
		if($clearErrors){$this->clearErrors(); $this->maxErrors = -1;}

		$queues = $this->pop($length);

		//过滤任务
		$queues = array_filter($queues, function($queue){
			//检查任务
			if(!isset($queue['queue']) || empty($queue['queue'])){
				$this->addError('The task is not set, queue:' . JsonHelper::encode($queue));
				return false;
			}

			//检查worker
			if(!isset($queue['worker']) || empty($queue['worker'])){
				$this->addError('The worker is not set, queue:' . JsonHelper::encode($queue));
				return false;
			}

			return true;
		});
		if(0 == count($queues)){return;}

		//根据worker分类
		$queuesClass = [];
		foreach ($queues as $queue) {
			$class = call_user_func($this->serializer[0], $queue['worker']);
			$queuesClass[$class]['queues'][] = $queue['queue'];
			$queuesClass[$class]['worker'] = $queue['worker'];
		}

		$executeHasErrors = false;
		foreach ($queuesClass as $class => $collection) {
			try {
				$worker = $this->getWorker($collection['worker']);
				call_user_func($worker, $collection['queues'], [$this, 'confirm']);
			} catch (\Exception $e) {
				$this->addError($e->getMessage());
			}
		}
	}

	public function confirm($queue)
	{
		return true;
	}

	abstract protected function pushQueue($queue);
	abstract protected function popQueues($length);
    abstract protected function length();
    abstract protected function flush();

    public function hasErrors()
    {
    	return 0 < count($this->_errors);
    }

    public function getLastError()
    {
    	if(!$this->hasErrors()){return null;}

    	return current($this->_errors);
    }

    public function getErrors()
    {
    	return $this->_errors;
    }

    public function addError($error)
    {
    	array_unshift($this->_errors, $error);
    }

    public function clearErrors()
    {
    	$this->_errors = [];
    }

    protected function getWorker($worker)
	{
		$method = $worker['method'];
		unset($worker['method']);

		$workerClassKey = call_user_func($this->serializer[0], $worker);
		if(!isset($this->_workerObjects[$workerClassKey])){
			$this->_workerObjects[$workerClassKey] = Yii::createObject([
				'class' => $worker['class']
			]);

			//保证缓存的worker不会超过限制
			while (count($this->_workerObjects) > $this->maxCacheWorker) {
				array_pop($this->_workerObjects);
			}
		}

		return [
			$this->_workerObjects[$workerClassKey],
			$method
		];
	}
}