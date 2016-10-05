<?php
namespace strong\redis;

/**
 *
 *
 * Class Connection
 * @package strong\redis
 */
class Connection extends \yii\redis\Connection
{
    /**
     * zrevrange方法重新实现，返回值格式化
     *
     * @param $redisKey
     * @param $start
     * @param $end
     * @param bool $withscores
     * @return array
     * [
     *      '域'=>'值',
     *      '域'=>'值',
     * ]
     *
     */
    public function zrevrange()
    {
        $params = func_get_args();
        $withscores = isset($params[3]) && 'WITHSCORES' == strtoupper($params[3]);
        $tmpResult = $this->executeCommand(__FUNCTION__, $params);

        $result = [];
        if($withscores === true && !empty($tmpResult)){
            $tmpResult = array_chunk($tmpResult, 2);
            foreach($tmpResult as $key => $val){
                $result[$val[0]] = $val[1];
            }
        }else{
            $result = $tmpResult;
        }
        return $result;
    }

    /**
     * hgetall方法重新实现，返回值格式化
     *
     * @param $redisKey
     * @return array
     * [
     *      '域'=>'值',
     *      '域'=>'值',
     * ]
     */
    public function hgetall()
    {
        $params = func_get_args();
        $tmpResult = $this->executeCommand(__FUNCTION__, $params);

        $result = [];
        if(!empty($tmpResult)){
            $tmpResult = array_chunk($tmpResult, 2);
            foreach($tmpResult as $key => $val){
                $result[$val[0]] = $val[1];
            }
        }else{
            $result = $tmpResult;
        }
        return $result;
    }
}
