<?php
namespace strong\geoip;

use yii;

require(__DIR__ . '/GeoIP/GeoIp.php');

class GeoIp extends \yii\base\Object
{
    public $dataFile;

    public $_gi;

    public function init()
    {
        $this->_gi = geoip_open($this->dataFile, GEOIP_STANDARD);
    }

    function getCountry($ip)
    {
        // 获取国家代码
        $ret['code'] = geoip_country_code_by_addr($this->_gi, $ip);

        // 获取国家名称
        $ret['name'] = geoip_country_name_by_addr($this->_gi, $ip);

        // 获取国家ID
        $ret['id'] = geoip_country_id_by_addr($this->_gi, $ip);

        return $ret;
    }

    public function __destruct()
    {

    }
}