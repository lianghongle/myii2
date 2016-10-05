<?php
namespace strong\jnear;

use Yii;
use strong\helpers\TimeHelper;
use strong\helpers\FileHelper;

include(__DIR__ . '/sdk/php/Sign.php');
class Server extends \yii\base\Object
{
	public $redis;

	public $db;

	public function getTicketByAppid($appid, $apiList)
    {
        return md5(json_encode(array(
            'apiList' => $apiList,
            'nonce' => '0b3144dd79e2d99aa31018ef017f73d5',
            'appid' => $appid
        )));
    }

    public function getDomainByAppid($appid)
    {
        $domains = array(
            'd690f0117ab84d88f192023da2265013' => 'test.radar.ids111.com:81',
            '1368b61d5be483fe4e7d9fbe2406e71c' => 'act.radar.ids111.com:81',
            'f1642795119ff4941cddc630434b5bff' => 'act.radar.ids111.com',
            'c1b91423f724b7b6f8ddf60c55f80853' => 'act.near.hk',
            '8c654dae831d19cb35e104204d7a1801' => 'hugh.webview.com',
        );
        return isset($domains[$appid]) ? $domains[$appid] : false;
    }

    public function checkSign($signPackage, $sign)
    {
    	$signParams = array_flip(array('appid', 'timestamp', 'nonce', 'uri', 'api_list'));
    	$signPackage = array_intersect_key($signPackage, $signParams);

    	//验证签名
    	$signPackage['ticket'] = $this->getTicketByAppid($signPackage['appid'], $signPackage['api_list']);
    	if(
    		count($signParams) != count($signPackage)
    		|| in_array(true, array_map(function($item){return empty($item);}, $signPackage), true)
    		|| $sign != Sign::createSign($signPackage)
    	){
    		return array('c' => 1, 'msg' => '签名失败');
    	}

        //验证APPID
        $domain = $this->getDomainByAppid($signPackage['appid']);
        $urlParse = parse_url(strtolower($signPackage['uri']));
        if(false == $domain || $urlParse['host'] != $domain){
        	return array('c' => 2, 'msg' => 'appid不匹配');
        }

        $this->ajaxReturn(array('c' => '000000', 'msg' => 'ok'));
    }
}