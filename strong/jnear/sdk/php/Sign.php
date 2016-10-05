<?php

class Sign
{
	public function argSort($para){
		ksort($para);
		reset($para);
		return $para;
	}

	public function httpBuildQuery($para)
	{
		$arg  = "";
		while (list($key, $val) = each($para)){
			$val = empty($val) ? '' : strval($val);
			$arg .= $key ."=" .urlencode($val) . "&";
		}
		$arg = substr($arg, 0, count($arg) - 2);
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		return $arg;
	}

	public function createSign($para)
	{
		return md5(crc32(static::httpBuildQuery(static::argSort($para))));
	}

	public function createNonce($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
		$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
}