<?php
namespace strong\queue;

use yii\di\Instance;
use yii\base\UnknownMethodException;

/**
 * class Test extends \strong\queue\Worker
 *{
 *   public $queue = 'radarQueue';
 *
 *   public function execute(array $queues, $confirmCallback)
 *   {
 *       $results = [];
 *       foreach ($queues as $queue) {
 *           echo $queue, PHP_EOL;
 *
 *          $results[] = true;
 *           true AND call_user_func($confirmCallback, $queue);
 *       }
 *       return $results;
 *   }
 *}
 */

class Worker extends \yii\base\Object
{
	public $queue;
	public $serializer = ['serialize', 'unserialize'];

	public function init()
    {
        parent::init();
    	$this->queue = Instance::ensure($this->queue, Queue::className());
    }

    public function push($queue)
	{
		return $this->queue->push([
            'queue' => $queue,
            'worker' => ['class' => static::className(), 'method' => 'execute']
        ]);
	}

	public function execute(array $queues, $confirmCallback)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

	protected static $_modelInstance = [];

    public static function model()
    {
        $class = static::className();
        if(!isset(static::$_modelInstance[$class])){
            static::$_modelInstance[$class] = new static;
        }
        return static::$_modelInstance[$class];
    }
}