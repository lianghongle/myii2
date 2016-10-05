<?php
require_once('jce.php');
require_once('wup.php');
require_once('qmf_protocal_define_wup.php');
//LDmfUpstream Seq,Appid,Uin,ServiceCmd,deviceType,Token,IpInfo,BusiBuff 收到的消息包
//LDmfPushReq SeqNo,Uin,Appid,ServiceCmd,Time,msg_receiver,Data,DeviceToken  服务端进行push时的发包
require_once('chat_wup.php');
//MsgInfo type, sub_type, receiver, sender, gender, avatar, nickname. content, busi_ext, group_id,group_name 具体的消息体
//note:对msginfo里的receiver字段约定：
//服务端收到的包里，receiver代表具体的接收对象(uid或者群聊时的群id)；而服务端push出去的包里，receiver代表发送者id

class wupMessage {
    private static $_fp = array();
    private static $_t = array();
    private static $_reconnect_time = 600; //tcp连接空闲10分就重连

    public static function decodeMsgFromStream($data)
    {
        try{
        $wupdec = new wup_unipacket;
        $wupdec->_decode($data);
        $req = new LDmfUpstream;
        $wupdec->get('dispatch_req',$req);

        //echo "appid:".$req->Appid->val." uid:".$req->Uin->val." ServiceCmd:".$req->ServiceCmd->val." buff size:".$req->BusiBuff->size()."\n";
        if ($req->ServiceCmd->val != 'sendmsg') {
            return ;
        }
        //按vincent.meng要求修改。eric.cai
        //start
        $msg = new MsgInfo;
        if($req->isJce->val == 0) {
            $busi_data = $req->BusiBuff->get_val();
            $busi_wupdec = new wup_unipacket;
            $busi_wupdec->_decode($busi_data);

            //echo '--end _decode MsgInfo,service='.$busi_wupdec->getServantName()->val.' fun='.$busi_wupdec->getFuncName()->val."\n";

            $busi_wupdec->get('busi_msg', $msg);
        }
        else if($req->isJce->val == 1) {
                $newPkg = pack("c", 10).$busi_data;
                $msg->read($newPkg, 0);
        }
        //end
        /*
        $msg = new MsgInfo;
        $busi_data = $req->BusiBuff->get_val();
        $busi_wupdec = new wup_unipacket;
        $busi_wupdec->_decode($busi_data);
        // var_dump($busi_data);die;
        $busi_wupdec->get('busi_msg', $msg);
        */
        $msg_arr = array(
            'appid' => $req->Appid->val,
            'uid' => $req->Uin->val,
            'type' => $msg->type->val,
            'sub_type' => $msg->sub_type->val,
            'receiver' => $msg->receiver->val,
            //'sender' => $msg->sender->val,
            'gender' => $msg->gender->val,
            'avatar' => $msg->avatar->val,
            'nickname' => $msg->nickname->get_val(),
            'content' => $msg->content->get_val(),
            'busi_ext' => $msg->busi_ext->get_val(),
            //'group_id' => $msg->group_id->val,
            //'group_name' => $msg->group_name->get_val(),
            'Seq' => $req->Seq->val
        );
        return $msg_arr;
        }catch (JCEException $e) {
            //echo $e->getMessage();
            return false;
        }
    }

    public static function encodeMsgBody($data)
    {
        $msg = new MsgInfo; //type, sub_type, uid, gender, avatar, nickname, content, time, busi_ext
        $msg->type->val = intval($data['type']);
        $msg->sub_type->val = intval($data['sub_type']);
        $msg->receiver->val = intval($data['uid']);
        $msg->gender->val = intval($data['gender']);
        $msg->avatar->val = $data['avatar'];
        $msg->nickname->push_back($data['nickname']);
        $msg->content->push_back($data['content']);
        $msg->time->val = intval($data['time']);
        if (!empty($data['busi_ext'])) {
            $msg->busi_ext->push_back($data['busi_ext']);
        }
        $busi_wupenc = new wup_unipacket;
        $busi_wupenc->put('busi_msg', $msg);
        $busi_wupenc->_encode($msgBuffer);

        return $msgBuffer;
    }

    //进行push推送
    public static function sendPushMsg($uid, $appid, $targets, $BusiBuff, $pushServerConfig, $notificationMsg = '推送消息', $seqId="")
    {
        //构造push请求包体
        $push_req = new LDmfPushReq; //SeqNo,Uin,Appid,ServiceCmd,Time,msg_receiver,Data,DeviceToken, notification_msg
        $push_req->Uin->val             = intval($uid);
        $push_req->Appid->val           = intval($appid);
        $push_req->SeqNo->val           = intval($seqId);
        $push_req->ServiceCmd->val      = "pushmsg";
        $push_req->notification_msg->val = $notificationMsg;
        $push_req->Time->val = time();

        foreach($targets as $t_uid)
        {
            $sh = new c_int64;
            $sh->val = intval($t_uid);
            $push_req->msg_receiver->push_back($sh); //多个继续push
        }

        $push_req->Data->push_back($BusiBuff);

        $busi_wupenc = new wup_unipacket;
        $busi_wupenc->setRequestId($seqId);
        $busi_wupenc->setServantName('platform.pushsvr.pushmsg');
        $busi_wupenc->setFuncName('pushmsg');

        $pushBuffer = '';
        $busi_wupenc->put('req_k', $push_req);
        $busi_wupenc->_encode($pushBuffer);

        //获取配置的ip和端口
        $ip = $pushServerConfig['ip'];
        $port = $pushServerConfig['port'];

        //upd发送。服务支持udp和tcp
        /*
        $host = 'tcp://'.$ip;
        $fp   = fsockopen($host , $port , $errno, $errstr, 3);
        stream_set_timeout($fp , 1);
        if(!$fp){
            //echo 'open sock for ip'.$host.'port '.$port.' failed!';
            return false;
        } else {
            //无需等待响应
            fputs($fp, $pushBuffer);
            fclose($fp);
            return true;
        }
        */
        //如果10分钟没有操作就重连一次
        $t = time();
        if(!isset(self::$_fp["{$ip}:{$port}"])) {
            self::$_fp["{$ip}:{$port}"] = self::connectTcpServer($ip, $port);
        }elseif( (($t - self::$_t["{$ip}:{$port}"]) >= self::$_reconnect_time) ){
            echo date("Y-m-d H:i:s"). ' - reconnect tcp - ' . "[$t" . " - " . self::$_t . "]" . "\n";
            fclose(self::$_fp["{$ip}:{$port}"]);
            self::$_fp["{$ip}:{$port}"] = self::connectTcpServer($ip, $port);
        }
        //发送返回值为0时，则重连server再发送。
        if(!($l = self::putBuffer(self::$_fp["{$ip}:{$port}"], $pushBuffer))) {
            //发送不成功则重连tcp server.
            fclose(self::$_fp["{$ip}:{$port}"]);
            self::$_fp["{$ip}:{$port}"] = self::connectTcpServer($ip, $port);
            if(!($l = self::putBuffer(self::$_fp["{$ip}:{$port}"], $pushBuffer))) {
                //不成功则返回false
                return 0;
            }
        }
        self::$_t["{$ip}:{$port}"] = time();
        return $l;
    }

    static private function putBuffer($fp, $pushBuffer) {
        $len = fputs($fp, $pushBuffer);
        if(strlen($pushBuffer) != $len) {
            echo date("Y-m-d H:i:s"). ' - ' . bin2hex($pushBuffer) . "\n";
            return false;
        }else{
            return $len;
        }
    }

    static private function connectTcpServer($ip, $port) {
        $host = 'tcp://'.$ip;
        $fp   = pfsockopen($host , $port , $errno, $errstr, 20);
        if (!$fp) {
            echo date("Y-m-d H:i:s"). ' - ' ."$errstr ($errno) \n";
        }
        return $fp;
    }
}