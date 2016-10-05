<?php

namespace strong\helpers;

use yii;
use yii\base\Exception;

class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * 安全读取一个文件;
     * @param  string  $file   文件名
     * @param  boolean $lockNB 是否堵塞;
     * @return string 文件内容
     */
    public static function getContents($file, $lockNB = false)
    {
        $contents = false;
        if(false !== ($fp = fopen($file, 'r'))){
            if(flock($fp, $lockNB ? (LOCK_SH | LOCK_NB) : LOCK_SH)){
                $contents = fread($fp, filesize($file));
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
        return $contents;
    }

    /**
     * 安全写入文件内容, 文件不存在就创建文件;
     * @param  string  $file   文件名
     * @param  string  $string   写入的内容
     * @param  boolean $isAppend 是否使用追加的方式写入;
     * @param  boolean $lockNB 是否堵塞;
     * @param  [type]  $fileMode 创建文件的赋予的权限;
     * @return boolean;
     */
    public static function putContents($file, $string, $isAppend = false, $lockNB = false, $fileMode = null)
    {
        $result = false;
        if(false !== ($fp = fopen($file, $isAppend ? 'a' : 'w'))){
            if(flock($fp, $lockNB ? (LOCK_EX | LOCK_NB) : LOCK_EX)){
                $result = fwrite($fp, $string);
                null === $fileMode OR chmod($file, $fileMode);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

        return $result;
    }

    protected $alreadyIncludeFile = [];
    public static function import($file, $again = false)
    {
        if(!isset(static::$alreadyIncludeFile[$file]) || $again){
            include($path);
            static::$alreadyIncludeFile[$file] = true;
        }
    }

    /**
     * 获取文件扩展名
     *
     * @param $file
     * @return mixed
     */
    public static function getExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

}
