<?php
namespace strong\helpers;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * 数组过滤
     *
     * @param array $array          待处理数组
     * @param array $filterKeys     过滤参考key的数组
     * @param bool $retained        true，根据key保留；false，根据key移除
     * @return array
     */
    public static function filterByKeys(array $array, array $filterKeys, $retained = true)
    {
        if(empty($array) || empty($filterKeys)){
            return $array;
        }

        //保留的key数组
        $retainedKeys = array_filter(array_keys($array), function($key) use ($filterKeys, $retained) {
            return in_array($key, $filterKeys) ? $retained : !$retained;
        });
        $array = array_intersect_key($array, array_flip($retainedKeys));

        return $array;
    }

    /**
     * 方法unique,重写系统函数array_unique,该函数的效率更佳;
     *
     * @param arr array [必选]    传入的数组;
     *
     * @return array
     */
    public static function unique(array $array)
    {
        return array_flip(array_flip($array));
    }

    /**
     * 方法rand,随机在数组里面取一部分元素;
     *
     * @param arr array [必选]    传入的数组;
     * @param number int [必选] 返回的元素个数;
     *
     * @return array:
     */
    public static function rand(array $array, $number)
    {
        shuffle($array);
        return array_slice($array, 0, $number, true);
    }

    /**
     * 合并两个set, 组成一个新的set;
     * @param  array $setA      setA
     * @param  array $setB      setB
     * @param  callback $filterFunction  过滤规则, 默认不过滤;
     * @return array;
     */
    public static function appendToSet($set, $appendSet, $filterFunction = null)
    {
        $set  = static::unique(array_merge($set, $appendSet));
        return null === $filterFunction ? $set : array_filter($set, function($value) use ($filterFunction){
            return !call_user_func($filterFunction, $value);
        });
    }

    /**
     * array_map的升级版, 递归;
     * @param  array  $array    需要处理的数组
     * @param  type $callback 回调方法
     * @return array
     */
    public static function arrayMap(array $array, $callback)
    {
        foreach ($array as $key => $value) {
            if(is_array($value)){
                $array[$key] = call_user_func(__METHOD__, $value, $callback);
            }else{
                $array[$key] = call_user_func($callback, $value, $key);
            }
        }
        return $array;
    }

    public static function isComplex($array)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        foreach ($array as $value) {
            if(!is_scalar($value) && null !== $value){
                return false;
            }
        }
        return true;
    }
}
