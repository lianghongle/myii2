<?php
namespace strong\helpers;

/**
 * 数学帮助类
 *
 * Class MathHelper
 * @package strong\helpers
 */
class MathHelper extends \yii\base\Object
{
    /**
     * 生成随机无符号浮点数
     *
     * @param int $min          最小值
     * @param int $max          最大值
     * @param int $decimal      小数位数
     * @return int
     */
    public static function randomFloat($min = 0, $max = 100, $decimal = 2) {
//        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
        $float = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return round($float, $decimal);
    }

}
