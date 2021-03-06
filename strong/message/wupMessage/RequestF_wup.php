<?php

// **********************************************************************
// This file was generated by a TAF parser!
// TAF version 2.1.4.1 by WSRD Tencent.
// Generated from `RequestF.jce'
// **********************************************************************


require_once('jce.php');

class RequestPacket extends c_struct
{
    public $iVersion;
    public $cPacketType;
    public $iMessageType;
    public $iRequestId;
    public $sServantName;
    public $sFuncName;
    public $sBuffer;
    public $iTimeout;
    public $context;
    public $status;

    public function __clone()
    {
        $this->iVersion = clone $this->iVersion;
        $this->cPacketType = clone $this->cPacketType;
        $this->iMessageType = clone $this->iMessageType;
        $this->iRequestId = clone $this->iRequestId;
        $this->sServantName = clone $this->sServantName;
        $this->sFuncName = clone $this->sFuncName;
        $this->sBuffer = clone $this->sBuffer;
        $this->iTimeout = clone $this->iTimeout;
        $this->context = clone $this->context;
        $this->status = clone $this->status;
    }

    public function __construct()
    {
        $this->iVersion = new  c_short;
        $this->cPacketType = new  c_char;
        $this->iMessageType = new  c_int;
        $this->iRequestId = new  c_int;
        $this->sServantName = new  c_string;
        $this->sFuncName = new  c_string;
        $this->sBuffer = new  c_vector (new c_char);
        $this->iTimeout = new  c_int;
        $this->context = new  c_map (new c_string,new c_string);
        $this->status = new  c_map (new c_string,new c_string);
    }

    public function get_class_name()
    {
        return "taf.RequestPacket";
    }

    public function writeTo(&$_out,$tag=0)
    {
        $this->iVersion->write($_out,1);
        $this->cPacketType->write($_out,2);
        $this->iMessageType->write($_out,3);
        $this->iRequestId->write($_out,4);
        $this->sServantName->write($_out,5);
        $this->sFuncName->write($_out,6);
        $this->sBuffer->write($_out,7);
        $this->iTimeout->write($_out,8);
        $this->context->write($_out,9);
        $this->status->write($_out,10);
    }
    public function readFrom(&$_in,$tag=0,$isRequire = true)
    {
        $this->iVersion->read($_in , 1, true);
        $this->cPacketType->read($_in , 2, true);
        $this->iMessageType->read($_in , 3, true);
        $this->iRequestId->read($_in , 4, true);
        $this->sServantName->read($_in , 5, true);
        $this->sFuncName->read($_in , 6, true);
        $this->sBuffer->read($_in , 7, true);
        $this->iTimeout->read($_in , 8, true);
        $this->context->read($_in , 9, true);
        $this->status->read($_in , 10, true);
        $this->_skip_struct($_in);
    }
};

class ResponsePacket extends c_struct
{
    public $iVersion;
    public $cPacketType;
    public $iRequestId;
    public $iMessageType;
    public $iRet;
    public $sBuffer;
    public $status;
    public $sResultDesc;

    public function __clone()
    {
        $this->iVersion = clone $this->iVersion;
        $this->cPacketType = clone $this->cPacketType;
        $this->iRequestId = clone $this->iRequestId;
        $this->iMessageType = clone $this->iMessageType;
        $this->iRet = clone $this->iRet;
        $this->sBuffer = clone $this->sBuffer;
        $this->status = clone $this->status;
        $this->sResultDesc = clone $this->sResultDesc;
    }

    public function __construct()
    {
        $this->iVersion = new  c_short;
        $this->cPacketType = new  c_char;
        $this->iRequestId = new  c_int;
        $this->iMessageType = new  c_int;
        $this->iRet = new  c_int;
        $this->sBuffer = new  c_vector (new c_char);
        $this->status = new  c_map (new c_string,new c_string);
        $this->sResultDesc = new  c_string;
    }

    public function get_class_name()
    {
        return "taf.ResponsePacket";
    }

    public function writeTo(&$_out,$tag=0)
    {
        $this->iVersion->write($_out,1);
        $this->cPacketType->write($_out,2);
        $this->iRequestId->write($_out,3);
        $this->iMessageType->write($_out,4);
        $this->iRet->write($_out,5);
        $this->sBuffer->write($_out,6);
        $this->status->write($_out,7);
        $this->sResultDesc->write($_out,8);
    }
    public function readFrom(&$_in,$tag=0,$isRequire = true)
    {
        $this->iVersion->read($_in , 1, true);
        $this->cPacketType->read($_in , 2, true);
        $this->iRequestId->read($_in , 3, true);
        $this->iMessageType->read($_in , 4, true);
        $this->iRet->read($_in , 5, true);
        $this->sBuffer->read($_in , 6, true);
        $this->status->read($_in , 7, true);
        $this->sResultDesc->read($_in , 8, false);
        $this->_skip_struct($_in);
    }
};
?>