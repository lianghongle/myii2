<?php
namespace strong\db;

use yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\JsonHelper;

class ShardingActiveRecord extends ActiveRecord
{
    /**
     * 当前的table;
     */
    protected static $_currentTable;

    /**
     * 使用的连接组件
     */
    protected static $_currentDb;

    /**
     * 模板的占位符
     */
    protected static $_placeholder = [
        'table' => '{{table}}',
        'database' => '{{database}}',
        'dbComponent' => '{{dbComponent}}',
    ];

    /**
     * 默认的sharding字段的值
     * @return array
     */
    public static function defaultShardingAttributes()
    {
        $attributes = [];
        foreach (static::shardingKeys() as $key) {$attributes[$key] = 1;}
        return $attributes;
    }

    /**
     * 返回分库分表的规则;
     * [
     *     static::$_placeholder['table'] => 0,
     *     static::$_placeholder['database'] => 0,
     *     static::$_placeholder['dbComponent'] => '',
     *  ];
     *
     * @return array
     */
    public static function shardingRules($attributes)
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * 返回分库分表的字段值, 返回值必须是一个数组;
     *['uid']
     *
     * @return array
     */
    public static function shardingKeys()
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * 定义表模板;
     *
     * sprintf(
     *     'radar_user_profile_%s.user_profile_%s',
     *     static::$_placeholder['database'],
     *     static::$_placeholder['table']
     * )
     *
     * @return string
     */
    public static function tableTemplate()
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * 定义DB实例模板;
     *
     * sprintf('radar%s', static::$_placeholder['dbComponent'])
     *
     * @return string
     */
    public static function dbTemplate()
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * 初始化sharding, 设置表和连接组件
     * @param  分库分表的字段值
     */
    public static function initSharding($shardingAttributes = null)
    {
        $shardingInfo = static::getShardingInfo($shardingAttributes);
        static::$_currentDb = $shardingInfo['db'];
        static::$_currentTable = $shardingInfo['table'];
    }

    /**
     * 所有分库分表的操作全部需要经过这个函数, 就是一层分库分表的包装
     * @param  $shardingAttributes 分库分表的字段值
     * @param  $callback 具体操作封装成的回调, 回调必须包含返回值;
     * @return 回调的返回值
     */
    public static function sharding($shardingAttributes = null, $callback)
    {
        static::initSharding($shardingAttributes);
        return call_user_func($callback);
    }

    /**
     * 根据分库分表的字段值返回实际的表和DB;
     * @param  $shardingAttributes  分库分表的字段值;
     * @return array
     */
    public static function getShardingInfo($shardingAttributes = null)
    {
        $shardingAttributes = null === $shardingAttributes ? static::defaultShardingAttributes() : $shardingAttributes;

        $shardingKeys = static::shardingKeys();
        if(!is_array($shardingAttributes)){
            $shardingAttributes = [current($shardingKeys) => $shardingAttributes];
        }

        if(count($shardingAttributes) != count($shardingKeys)){
            throw new InvalidParamException(__METHOD__ . ' $shardingAttributes Invalid Param Exception');
        }

        $shardingSerial = static::shardingRules($shardingAttributes);
        return [
            'table' => strtr(static::tableTemplate(), $shardingSerial),
            'db' => strtr(static::dbTemplate(), $shardingSerial)
        ];
    }

    public static function tableName()
    {
        if(null === static::$_currentTable){
            static::initSharding();
        }
        return static::$_currentTable;
    }

    public static function getDb()
    {
        if(null === static::$_currentDb){
            static::initSharding();
        }
        return Yii::$app->get(static::$_currentDb);
    }
}