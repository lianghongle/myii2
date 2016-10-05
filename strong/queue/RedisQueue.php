<?php
namespace strong\queue;

use yii\di\Instance;
use yii\redis\Connection;

class RedisQueue extends Queue
{
	public $redis;

	public $queueKeyPrefix = '';

	protected $queueKey;

	public function init()
    {
        parent::init();

        $this->redis = Instance::ensure($this->redis, Connection::className());
        $this->queueKey = $this->queueKeyPrefix . '_' . $this->queueName;
    }

	public function popQueues($length)
	{
		$queues = [];
		for ($i = 1; $i <= $length; $i++) {
			try {
				$queue = $this->redis->executeCommand(
					$this->deduplication ? 'SPOP' : 'LPOP',
					[$this->queueKey]
				);
			} catch (\Exception $e) {
				$this->addError($e->getMessage());
				$queue = false;
				break;
			}
			if(null === $queue){break;}
			$queues[] = $queue;
		}
		return $queues;
	}

	protected function pushQueue($queue)
	{
		try {
			return 1 <= $this->redis->executeCommand(
				$this->deduplication ? 'SADD' : 'RPUSH',
				[$this->queueKey, $queue]
			);
		} catch (\Exception $e) {
			$this->addError($e->getMessage());
			return false;
		}
	}

    public function length()
    {
    	try {
			return intval($this->redis->executeCommand(
				$this->deduplication ? 'SCARD' : 'LLEN',
				[$this->queueKey]
			));
		} catch (\Exception $e) {
			$this->addError($e->getMessage());
			return false;
		}
    }

    public function flush()
    {
    	try {
			$this->redis->executeCommand('DEL', [$this->queueKey]);
			return true;
		} catch (\Exception $e) {
			$this->addError($e->getMessage());
			return false;
		}
    }

    public function confirm($queue)
	{
		return true;
	}
}