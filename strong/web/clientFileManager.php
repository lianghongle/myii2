<?php

namespace strong\web;

/**
 * 客户端文件管理器;
 *
 *
 * @author hugh
 *
 * 配置:
 *[
 *           'class' => '\strong\web\clientFileManager',
 *           'route' => 'http://static.a165.local',
 *           'resource' => [
 *               'css' => [
 *                   'version' => '1.0',
 *                   'fileList' => [
 *                       'bootstrap' => ['path' => '/common/bootstrap/css/bootstrap.css'],
 *                       'common' => ['path' => '/novel/css/common.css'],
 *                       'index' => ['path' => '/novel/css/index.css']
 *                   ],
 *               ],
 *
 *               'js' => [
 *                   'fileList' => [
 *                       //'jquery' => ['path' => '/common/jquery/jquery-1.11.1.js'],
 *                       //'bootstrap' => ['path' => '/common/bootstrap/js/bootstrap.min.js'],
 *                   ],
 *               ],
 *           ],
 *],
 */
class clientFileManager  extends \yii\base\Object{
    /**
     * 静态文件域名;
     * @var string;
     */
    public $route = '';

    /**
     * 静态文件的版本信息;
     * @var int
     */
    public $version = '';

    /**
     * 资源列表;
     * @var array;
     */
    public $resource = [];

    /**
     * 资源版本对应的key;
     * @var array;
     */
    public $versionKey = 'v';

    /**
     * 组合css的地址;
     *
     * @param $key;
     * @return url;
     */
    public function css($key){
        return $this->analysis('css', $key);
    }

    /**
     * 组合js的地址;
     *
     * @param $key;
     * @return url;
     */
    public function js($key){
        return $this->analysis('js', $key);
    }

    /**
     * 组合imgage的地址;
     *
     * @param $key;
     * @return url;
     */
    public function image($path){
        return $this->analysis('image', null, $path);
    }

    /**
     * 分析组合相对应的url地址;
     *
     * @param $type
     * @param $key
     * @param $path
     * @return array;
     */
    public function analysis($type, $key = null, $path = null){
        $resource = isset($this->resource[$type]) ? $this->resource[$type] : array();
        $version = isset($resource['version']) ? $resource['version'] : $this->version;
        $route = isset($resource['route']) ? $resource['route'] : $this->route;
        $versionKey = isset($resource['versionKey']) ? $resource['versionKey'] : $this->versionKey;

        if(!is_null($key) && isset($resource['fileList']) && isset($resource['fileList'][$key])){
            $resource = $resource['fileList'][$key];
            $version = isset($resource['version']) ? $resource['version'] : $version;
            $route = isset($resource['route']) ? $resource['route'] : $route;
            $versionKey = isset($resource['versionKey']) ? $resource['versionKey'] : $versionKey;
            $path = $resource['path'];
        }

        return static::reintegrating([
            'route' => $route,
            'path' => $path,
            'versionKey' => $versionKey,
            'version' => $version,
        ]);
    }

    /**
     * 组合响应的url信息;
     *
     * @param array $data
     * @return boolean|string
     */
    public static function reintegrating(array $data){
        $route = isset($data['route']) ? strval($data['route']) : '';
        $path = isset($data['path']) ? strval($data['path']) : '';
        $versionKey = isset($data['versionKey']) ? strval($data['versionKey']) : '';
        $version = isset($data['version']) ? strval($data['version']) : '';

        if(empty($path)){
            return false;
        }

        if(!empty($version)){
            $parse = parse_url($path);
            $parse['query'] = isset($parse['query']) && !empty($parse['query']) ? $parse['query'] : "{$versionKey}={$version}";
            parse_str($parse['query'], $parseSon);
            if(!array_key_exists($versionKey, $parseSon)){$parseSon[$versionKey] = $version;}
            $path = $parse['path']. '?' . http_build_query($parseSon);
        }

        return "{$route}{$path}";
    }
}
