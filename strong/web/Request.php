<?php

namespace strong\web;

use strong\helpers\CheckHelper;
use strong\helpers\ArrayHelper;

class Request extends \yii\web\Request
{
    /**
     *
     * @param unknown $name
     * @param string $defaultValue
     * @return Ambigous <string, unknown>
     */
    public function postInt($name, $defaultValue = null)
    {
        $param = $this->post($name, $defaultValue);

        return CheckHelper::isDigit($param) ? $param : $defaultValue;
    }

    public function getInt($name, $defaultValue = null)
    {
        $param = $this->get($name, $defaultValue);

        return CheckHelper::isDigit($param) ? $param : $defaultValue;
    }

    public function getNumeric($name, $defaultValue = null)
    {
        $param = $this->get($name, $defaultValue);

        return CheckHelper::isNumeric($param) ? $param : $defaultValue;
    }

    public function postNumeric($name, $defaultValue = null)
    {
        $param = $this->post($name, $defaultValue);

        return CheckHelper::isNumeric($param) ? $param : $defaultValue;
    }

    public function trimPost($name = null, $defaultValue = null)
    {
        $param = $this->post($name, $defaultValue);

        return $defaultValue === $param && null === $name ? $param : ArrayHelper::trim($param);
    }

    public function trimGet($name = null, $defaultValue = null)
    {
        $param = $this->get($name, $defaultValue);

        return $defaultValue === $param && null === $name ? $param : ArrayHelper::trim($param);
    }

    public function only(array $names, $type = null)
    {
        $data = [];
        foreach ($names as $name) {
            if ('get' == $type) {
                $data[$name] = $this->get($name);
            } elseif ('post' == $type) {
                $data[$name] = $this->post($name);
            }
        }
        return $data;
    }

    public function getIsMobile()
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $mobileAgents = ['240x320', 'acer', 'acoon', 'acs-', 'abacho', 'ahong', 'airness', 'alcatel', 'amoi', 'android', 'anywhereyougo.com', 'applewebkit/525', 'applewebkit/532', 'asus', 'audio', 'au-mic', 'avantogo', 'becker', 'benq', 'bilbo', 'bird', 'blackberry', 'blazer', 'bleu', 'cdm-', 'compal', 'coolpad', 'danger', 'dbtel', 'dopod', 'elaine', 'eric', 'etouch', 'fly', 'fly_', 'fly-', 'go.web', 'goodaccess', 'gradiente', 'grundig', 'haier', 'hedy', 'hitachi', 'htc', 'huawei', 'hutchison', 'inno', 'ipad', 'ipaq', 'ipod', 'jbrowser', 'kddi', 'kgt', 'kwc', 'lenovo', 'lg', 'lg2', 'lg3', 'lg4', 'lg5', 'lg7', 'lg8', 'lg9', 'lg-', 'lge-', 'lge9', 'longcos', 'maemo', 'mercator', 'meridian', 'micromax', 'midp', 'mini', 'mitsu', 'mmm', 'mmp', 'mobi', 'mot-', 'moto', 'nec-', 'netfront', 'newgen', 'nexian', 'nf-browser', 'nintendo', 'nitro', 'nokia', 'nook', 'novarra', 'obigo', 'palm', 'panasonic', 'pantech', 'philips', 'phone', 'pg-', 'playstation', 'pocket', 'pt-', 'qc-', 'qtek', 'rover', 'sagem', 'sama', 'samu', 'sanyo', 'samsung', 'sch-', 'scooter', 'sec-', 'sendo', 'sgh-', 'sharp', 'siemens', 'sie-', 'softbank', 'sony', 'spice', 'sprint', 'spv', 'symbian', 'talkabout', 'tcl-', 'teleca', 'telit', 'tianyu', 'tim-', 'toshiba', 'tsm', 'up.browser', 'utec', 'utstar', 'verykool', 'virgin', 'vk-', 'voda', 'voxtel', 'vx', 'wap', 'wellco', 'wig browser', 'wii', 'windows ce', 'wireless', 'xda', 'xde', 'zte'];

        if (!empty($userAgent)) {
            foreach ($mobileAgents as $mobileAgent) {
                if (stristr($userAgent, $mobileAgent)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getBrowse()
    {
        $browseInfoString = isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
        $result = false;
        if ($browseInfoString) {
            if (false !== strpos($browseInfoString, 'MSIE 10')) $result = 'Internet Explorer 10';
            elseif (false !== strpos($browseInfoString, 'MSIE 9')) $result = 'Internet Explorer 9';
            elseif (false !== strpos($browseInfoString, 'MSIE 8')) $result = 'Internet Explorer 8';
            elseif (false !== strpos($browseInfoString, 'MSIE 7')) $result = 'Internet Explorer 7';
            elseif (false !== strpos($browseInfoString, 'MSIE 6')) $result = 'Internet Explorer 6';
            elseif (false !== strpos($browseInfoString, 'MSIE 5')) $result = 'Internet Explorer 5';
            elseif (false !== strpos($browseInfoString, 'MSIE')) $result = 'Internet Explorer';
            elseif (false !== strpos($browseInfoString, 'Firefox/3')) $result = 'Firefox 3';
            elseif (false !== strpos($browseInfoString, 'Firefox/2')) $result = 'Firefox 2';
            elseif (false !== strpos($browseInfoString, 'Firefox')) $result = 'Firefox';
            elseif (false !== strpos($browseInfoString, 'Chrome')) $result = 'Google Chrome';
            elseif (false !== strpos($browseInfoString, 'Safari')) $result = 'Safari';
            elseif (false !== strpos($browseInfoString, 'Opera')) $result = 'Opera';
        }
        return $result;
    }

    public function getOS()
    {
        $agent = isset ($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : false;
        $os = false;
        if ($agent) {
            $isWindows = (false !== stripos($agent, 'win'));

            if ($isWindows && false !== stripos($agent, '95')) $os = 'Windows 95';
            elseif ($isWindows && false !== stripos($agent, 'win 9x') && stripos($agent, '4.90')) $os = 'Windows ME';
            elseif ($isWindows && false !== stripos($agent, '98')) $os = 'Windows 98';
            elseif ($isWindows && false !== stripos($agent, 'nt 5.1')) $os = 'Windows XP';
            elseif ($isWindows && false !== stripos($agent, 'nt 5')) $os = 'Windows 2000';
            elseif ($isWindows && false !== stripos($agent, '32')) $os = 'Windows 32';
            elseif ($isWindows && false !== stripos($agent, 'NT 6.1')) $os = 'Windows 7';
            elseif ($isWindows && false !== stripos($agent, 'nt')) $os = 'Windows NT';
            elseif ($isWindows) $os = 'Windows';
            elseif (false !== stripos($agent, 'linux')) $os = 'Linux';
            elseif (false !== stripos($agent, 'unix')) $os = 'Unix';
            elseif (false !== stripos($agent, 'sun') && stripos($agent, 'os')) $os = 'SunOS';
            elseif (false !== stripos($agent, 'ibm') && stripos($agent, 'os')) $os = 'IBM OS/2';
            elseif (false !== stripos($agent, 'Mac') && stripos($agent, 'PC')) $os = 'Macintosh';
            elseif (false !== stripos($agent, 'PowerPC')) $os = 'PowerPC';
            elseif (false !== stripos($agent, 'AIX')) $os = 'AIX';
            elseif (false !== stripos($agent, 'HPUX')) $os = 'HPUX';
            elseif (false !== stripos($agent, 'NetBSD')) $os = 'NetBSD';
            elseif (false !== stripos($agent, 'BSD')) $os = 'BSD';
            elseif (false !== stripos($agent, 'OSF1')) $os = 'OSF1';
            elseif (false !== stripos($agent, 'IRIX')) $os = 'IRIX';
            elseif (false !== stripos($agent, 'FreeBSD')) $os = 'FreeBSD';
            elseif (false !== stripos($agent, 'teleport')) $os = 'teleport';
            elseif (false !== stripos($agent, 'flashget')) $os = 'flashget';
            elseif (false !== stripos($agent, 'webzip')) $os = 'webzip';
            elseif (false !== stripos($agent, 'offline')) $os = 'offline';
        }
        return $os;
    }

    function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }

    public function getClientIp()
    {
        // Gets the default ip sent by the user
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $step = 1;
            $direct_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Gets the proxy ip sent by the user
        $proxy_ip = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $step = 2;
            $proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else
            if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
                $step = 3;
                $proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
            } else
                if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
                    $step = 4;
                    $proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
                } else
                    if (!empty($_SERVER['HTTP_FORWARDED'])) {
                        $step = 5;
                        $proxy_ip = $_SERVER['HTTP_FORWARDED'];
                    } else
                        if (!empty($_SERVER['HTTP_VIA'])) {
                            $step = 6;
                            $proxy_ip = $_SERVER['HTTP_VIA'];
                        } else
                            if (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
                                $step = 7;
                                $proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
                            } else
                                if (!empty($_SERVER['HTTP_COMING_FROM'])) {
                                    $step = 8;
                                    $proxy_ip = $_SERVER['HTTP_COMING_FROM'];
                                }

        // Returns the true IP if it has been found, else FALSE
        if (empty($proxy_ip)) {
            // True IP without proxy
            $ip = $direct_ip;
        } else {
            $is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxy_ip, $regs);
            if ($is_ip && (count($regs) > 0)) {
                // True IP behind a proxy
                $ip = $regs[0];
            } else {
                // Can't define IP: there is a proxy but we don't have
                // information about the true IP
                $ip = $direct_ip;
            }
        }
        return $ip;
    }
}
