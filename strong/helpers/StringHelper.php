<?php

namespace strong\helpers;

use ArrayHelper;

class StringHelper extends \yii\helpers\StringHelper
{

    /**
     * 函数changeAutoCharset,改变字符的编码;
     *
     * @param contents   string|array [必须] 需要转行的数据;
     * @param from enum('gbk','utf-8') [可选] 原始编码,默认是@gbk编码;
     * @param to enum('gbk','utf-8') [可选] 目标编码,默认是@utf-8编码;
     *
     * @return string;
     *
     */
    public static function autoCharset($contents, $from = 'gbk', $to = 'utf-8', $changeKeyCharset = false)
    {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if(strtoupper($from) === strtoupper($to) || empty($contents) || (is_scalar($contents) && !is_string($contents))){return $contents;}

        if(is_string($contents)){
            return function_exists('mb_convert_encoding') ? mb_convert_encoding($contents, $to, $from) : (function_exists('iconv') ? iconv($from, $to, $contents) : $contents);
        }elseif(is_array($contents)){
            $_contents = array();
            foreach($contents as $key => $value){
                $_key = $changeKeyCharset ? call_user_func(__METHOD__, $key, $from, $to) : $key;
                $_contents[$_key] = call_user_func(__METHOD__, $value, $from, $to);
            }
            return $_contents;
        }else{
            return $contents;
        }
    }

    /**
     * 函数msubstr,实现中文截取字符串;
     *
     * @param   str string [必选] 需要截取的字符串;
     * @param   length int [必须] 截取字符的长度,按照一个汉字的长度算作一个字符;
     * @param   start string [可选] 从那里开始截取;
     * @param   suffix string [可选] 截取字符后加上的后缀,默认为@...;
     * @param   charset enum('gbk','utf-8') [可选] 字符的编码,默认为@utf-8;
     *
     * @return string;
     *
     */
    public static function msubstr($str, $length, $start = 0, $suffix = '...', $charset = 'utf-8')
    {
        switch($charset){
            case 'utf-8':
                $charLen = 3;
                break;
            case 'UTF8':
                $charLen = 3;
                break;
            default:
                $charLen = 2;
        }
        // 小于指定长度，直接返回
        if(strlen($str) <= ($length * $charLen)) return $str;

        if(function_exists("mb_substr")){
            $slice = mb_substr($str, $start, $length, $charset);
        }else if(function_exists('iconv_substr')){
            $slice = iconv_substr($str, $start, $length, $charset);
        }else{
            $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }
        return $slice . $suffix;
    }

    public static function uniqid($type = 1) {
        $uniqData = [
            'uniqid' => uniqid(rand(1, 1000000)),
            'microtime' => microtime(),
            'rand' => rand(1, 100000000000),
        ];

        $uniqid = md5(json_encode($uniqData));

        if(1 == $type){
            return $uniqid;
        }else{
            return crc32($uniqid);
        }
    }

    public static function appendToSet($set, $appendSet, $delimiter = ',', $filterFunction = null)
    {
        $appendSet = is_array($appendSet) ? $appendSet : explode($delimiter, trim($appendSet, $delimiter));
        $appendSet = is_array($set) ? $set : explode($delimiter, trim($set, $delimiter));

        return implode($delimiter, ArrayHelper::appendToSet($set, $appendSet, $filterFunction));
    }
}
