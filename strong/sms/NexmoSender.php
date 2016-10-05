<?php
namespace strong\sms;

use Yii;

class NexmoSender extends Sender
{
	public $senders;

	public $defaultSender;

	public $timeout = 25;

	public $authParams;

	protected function sending(array $mobiles, $content)
	{
		$responses = [];

		$mobilesGroupByDialingCode = [];
		foreach ($mobiles as $mobile => $dialingCode) {
			$mobilesGroupByDialingCode[$dialingCode][] = $mobile;
		}

		foreach ($mobilesGroupByDialingCode as $dialingCode => $mobiles) {
			//选择发送的方式
			$CSender = $this->senders[$this->defaultSender];
			foreach ($this->senders as $sender) {
				if(in_array($dialingCode, $sender['dialingCode'])){
					$CSender = $sender;
					break;
				}
			}

			//组合发送参数
			$params = $this->authParams;
			if($CSender['useText']){
				//生成内容
				if(is_array($content)){
					$contentView = $this->contentViews[$content['view']];
					if(!empty($content['params'])){
						$replace = [];
						foreach ($content['params'] as $paramKey => $param) {
							$replace['{{' . $paramKey . '}}'] = $param;
						}
						$contentView = strtr($contentView, $replace);
					}
				}else{
					$contentView = $content;
				}

				//加上签名
				if(!empty($this->signView)){
					$contentView = strtr($this->signView, ['{{content}}' => $contentView]);
				}
				$params['text'] = urlencode($contentView);
			}else{
				$params['pin'] = strval(is_array($content) ? $content['params']['code'] : $content);
			}

			//一个个的发送
			foreach ($mobiles as $mobile) {
				$params = array_merge($params, array('to' => $mobile));
				$response = $this->httpRequest($CSender['url'], $params);

				//分析结果
				$responseArray = json_decode($response, true);
				$responses[$mobile] = [
					'c' => (isset($responseArray['messages'][0]['status']) && '0' == $responseArray['messages'][0]['status']) ? 0 : 1,
					'msg' => $response
				];
			}
		}

		return $responses;
	}

	protected function httpRequest($url, array $params)
    {
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}