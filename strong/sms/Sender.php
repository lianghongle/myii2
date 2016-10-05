<?php
namespace strong\sms;

use Yii;
use strong\helpers\TimeHelper;
use strong\helpers\FileHelper;

abstract class Sender extends \yii\base\Object
{
	public $logFile;

	public $signView;

	public $contentViews;

	public $dirMode = 0775;

    public $fileMode;

	public function init()
	{
		parent::init();
		$this->logFile = Yii::getAlias($this->logFile);

		is_dir(dirname($this->logFile)) OR FileHelper::createDirectory($path, $this->dirMode);
	}

	abstract protected function sending(array $mobiles, $content);

	/**
	 * 发送短信, Nexmo发送的时候    content['params']一定要包含code元素
	 * @param  $mobiles 手机 [['手机号' => '区号'], ['12510771987' => '86']]
	 * @param  $content 可以为数组或者字符串,
	 *         			字符串: 直接发送,
	 *                  数组: 使用模板 ['view' => 'pin_zh_tw', 'params' => ['code' => '1111']],
	 * @return 0:全部成功, 1:全部失败, 2:部分失败
	 */
	public function send(array $mobiles, $content)
	{
		$start = TimeHelper::getSec();

		$result = $this->sending($mobiles, $content);

		if(null !== $this->logFile){
			$path = dirname($this->logFile);
			is_dir($path) OR FileHelper::createDirectory($path);

			$string = sprintf(
				'%s    time:%s content:%s results:%s' . PHP_EOL,
				TimeHelper::date('Y-d-m H:i:s.u'),
				TimeHelper::getSec() - $start,
				is_array($content) ? json_encode($content) : $content,
				json_encode($result)
			);
			FileHelper::putContents($this->logFile, $string, true, true);
		}

		$resultCodes = array_map(function($result){
			return $result['c'];
		}, $result);
		return [
			'c' => !in_array(1, $resultCodes, true) ? 0 : (in_array(0, $resultCodes, true) ? 1 : 2),
			'result' => $result
		];
	}
}