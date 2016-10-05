<?php

namespace strong\IdGenerator;

use Yii;
use strong\helpers\FileHelper;
use strong\helpers\JsonHelper;
use yii\base\NotSupportedException;

class HttpIdGenerator extends IdGenerator
{
    /**
     * 请求额url
     * @var string
     */
    public $url;

    /**
     * 请求需要的参数
     * @var array
     */
    public $params = [];

    /**
     * 请求超时时间
     */
    public $timeout = 5;

    /**
     * 请求方式
     * @var string
     */
    public $method = 'get';

    /**
     * 处理请求结果的回调
     */
    public $decodeResponseCallback;

    public function getId()
    {
        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL, $this->url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec ($ch);
        $result = 0 === curl_errno ($ch) ? $result : false;

        return call_user_func($this->decodeResponseCallback, $result, $this);
    }
}
