<?php
// $sdk = new JsSdk(array(
// 	'appid' 	=> '1f9ad5c5f1f771bc0a9b2bc1ef21eaa3',
// 	'secret' 	=> 'd29b18b589a179a3732fd4ae96320829',
// ));
// $sdk->getSignPackage(array('getProfile'));

include(__DIR__ . '/Sign.php');
class JNear{
	private $appid;
	private $secret;
	private $sign;

	public function __construct(array $data) {
		$this->appid = $data['appid'];
		$this->secret = $data['secret'];
		$this->sign = new sign;
	}

	public function getTicket($apiList)
	{
		return md5(json_encode(array(
			'nonce' => '0b3144dd79e2d99aa31018ef017f73d5',
			'appid' => $this->appid
		)));
	}

	public function getSignPackage($apiList)
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$port = 80 == $_SERVER['SERVER_PORT'] ? '' : ":{$_SERVER['SERVER_PORT']}";
    	$url = "{$protocol}{$_SERVER['HTTP_HOST']}{$port}{$_SERVER['REQUEST_URI']}";
    	$signPackage = array(
			"appid"     => $this->appid,
			"nonce"  	=> $this->sign->createNonce(),
			"timestamp" => time(),
			"uri"       => $url,
			'ticket' 	=> $this->getTicket($apiList),
			'api_list' 	=> implode(',', $apiList),
		);

    	$signPackage['sign'] = $this->sign->createSign($signPackage);
    	$signPackage['ticket'] = '';
    	$signPackage['api_list'] = $apiList;
    	return $signPackage;
	}
}