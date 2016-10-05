<?php

namespace strong\db;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;


class Command extends \yii\db\Command
{
    public function execute()
    {
        return $this->tryPdoException(['parent', 'execute'], func_get_args());
    }


    protected function queryInternal($method, $fetchMode = null)
    {
        return $this->tryPdoException(['parent', 'queryInternal'], func_get_args());
    }

    protected function tryPdoException($method, array $params = array())
    {
        $pdoParams = $this->params;
        try {
            return call_user_func_array($method, $params);
        } catch (\yii\db\Exception $e) {
            //获取PDO返回的的错误码
            if(empty($this->pdoStatement)){
                $pdoErrorCode  = $this->db->pdo->errorCode();
            }else{
                $pdoErrorCode  = $this->pdoStatement->errorCode();
            }

            //如果需要重连并且错误码符合重连的
            if(
                in_array($pdoErrorCode, $this->db->reconnectionpdoErrorCodes)
                && $this->db->canReconnection()
            ){
                Yii::warning("db execute failed: " . $e->getMessage(), "{$method[0]}:{$method[1]}");
                $this->cancel();
                $this->bindValues($pdoParams);
                $this->db->close();
                $this->db->open();
                return call_user_func_array(__METHOD__, func_get_args());
            }else{
                throw $e;
            }
        }
    }
}
