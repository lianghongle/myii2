<?php

// **********************************************************************
// This file was generated by a TAF parser!
// TAF version 3.0.0.29 by WSRD Tencent.
// Generated from `chat.jce'
// **********************************************************************


require_once('jce.php');

class MsgInfo extends c_struct
{
    public $type;
    public $sub_type;
    public $receiver;
    public $content;
    public $avatar;
    public $nickname;
    public $gender;
    public $busi_ext;
    public $time;

    public function __clone()
    {
        $this->type = clone $this->type;
        $this->sub_type = clone $this->sub_type;
        $this->receiver = clone $this->receiver;
        $this->content = clone $this->content;
        $this->avatar = clone $this->avatar;
        $this->nickname = clone $this->nickname;
        $this->gender = clone $this->gender;
        $this->busi_ext = clone $this->busi_ext;
        $this->time = clone $this->time;
    }

    public function __construct()
    {
        $this->type = new  c_int;
        $this->sub_type = new  c_int;
        $this->receiver = new  c_int64;
        $this->content = new  c_vector (new c_char);
        $this->avatar = new  c_string;
        $this->nickname = new  c_vector (new c_char);
        $this->gender = new  c_short;
        $this->busi_ext = new  c_vector (new c_char);
        $this->time = new  c_int64;
    }

    public function get_class_name()
    {
        return "ld.Bu.MsgInfo";
    }

    public function write(&$_out,$tag)
    {
        jce_header::_pack_header($_out,'c_struct_begin',$tag);
        $this->type->write($_out,0);
        $this->sub_type->write($_out,1);
        $this->receiver->write($_out,2);
        $this->content->write($_out,3);
        $this->avatar->write($_out,4);
        $this->nickname->write($_out,5);
        $this->gender->write($_out,6);
        $this->busi_ext->write($_out,7);
        $this->time->write($_out,8);
        jce_header::_pack_header($_out,'c_struct_end',0);
    }
    public function read(&$_in,$tag,$isRequire = true)
    {
        jce_header::_check_struct($_in,$type,$tag,$isRequire);
        jce_header::_unpack_header($_in,$type,$this_tag);
        $this->type->read($_in , 0, true);
        $this->sub_type->read($_in , 1, true);
        $this->receiver->read($_in , 2, true);
        $this->content->read($_in , 3, true);
        $this->avatar->read($_in , 4, false);
        $this->nickname->read($_in , 5, false);
        $this->gender->read($_in , 6, false);
        $this->busi_ext->read($_in , 7, false);
        $this->time->read($_in , 8, false);
        $this->_skip_struct($_in);
    }
}


?>
