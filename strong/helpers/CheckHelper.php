<?php

namespace strong\helpers;

final class CheckHelper
{
    /**
     * 函数checkSAPI,验证php的运行方式;
     * aolserver、apache、 apache2filter、apache2handler、 caudium、cgi,
     * cgi-fcgi、cli、 continuity、embed、 isapi、litespeed、 milter、nsapi、 phttpd、
     * pi3web、roxen、 thttpd、tux、webjames;
     *
     * @param serverAPI string [可选] 默认为cli,验证是否为命令行模式;
     *
     * @return bool;
     */
    public static function sapi($serverAPI = 'cli')
    {
        return strtolower(PHP_SAPI) == strtolower($serverAPI);
    }

    /**
     * 函数checkOS,验证当前环境是否为一个系统;
     *
     * @param   os 系统名字;
     *
     * @return bool;
     */
    public static function os($os = 'Win')
    {
        return strtolower(PHP_OS) == strtolower($os);
    }

    /**
     * 函数isUtf8,判断一个字符串的编码是否为UTF-8;
     *
     * @param str string [必须] 需要判断的字符;
     *
     * @return bool;
     *
     */
    public static function isUtf8($string)
    {
        //return json_encode(array($string)) != '[null]';
        return static::checkEncoding($string, 'UTF-8');
    }

    /**
     * 函数checkEncoding,判断一个字符串的编码,该函数待完善;
     *
     * @param
     *          str string [必须] 需要判断的字符;
     * @param
     *          encoding string [可选] 字符的参考编码,默认为UTF-8;
     *
     * @return bool;
     *
     */
    public static function encoding($str, $encoding = 'UTF-8')
    {
        $encodingType = array ('GB2312', 'UTF-8', 'ASCII', 'GBK');
        return mb_detect_encoding ($str, $encodingType) == strtoupper ($encoding);
    }

    /**
     * 函数isNumeral,判断一个变量是否为数字;
     *
     * @param str string [必须] 需要判断的字符;
     *
     * @return bool;
     *
     */
    public static function isDigit($string)
    {
        $string = is_numeric($string) ? strval($string) : false;
        return ctype_digit($string);
    }

    /**
     * 函数isEmail,判断是否是一个合法的邮箱;
     *
     * @param str string [必须] 需要判断的字符;
     * @param bool [可选] 是否判断域名,该功能只能在linux下使用,默认不判断;
     *
     * @return bool;
     *
     */
    public static function isEmail($string, $isStrict = false)
    {
        $result = filter_var($string, FILTER_VALIDATE_EMAIL);
        if($result && $isStrict && static::os('linux') && function_exists('getmxrr')){
            list($prefix, $domain) = explode('@', $string);
            $result = getmxrr($domain, $mxhosts);
        }
        return $result;
    }

    /**
     * 函数isUrl,判断是否为一个URL, 只有在200-299状态情况才算能够访问;
     *
     * @param str string [必须] 需要判断的字符;
     *
     * @return bool;
     *
     */
    public static function isUrl($url, $checkAccess = false)
    {
        if(!filter_var($url, FILTER_VALIDATE_URL)){return false;}

        if($checkAccess){
            $accessResult = get_headers($url);
            return 200 <= $accessResult[0] && 300 >= $accessResult[0];
        }

        return true;
    }
}
