<?php

namespace strong\helpers;

class VarDumperHelper extends \yii\helpers\VarDumper
{
    /*格式化数组输出*/
    public static function echopre($data = array())
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
