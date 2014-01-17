<?php
if (!defined('IN_KKFRAME')) exit();

class cloud {
	const API_ROOT = 'http://api.ikk.me/v2/';
	private static register(){
		/* ... */
	}
	public static is_remote_disabled(){
		/* ... */
	}
	public static request($api_name){
		if (!$api_name) throw new Exception('Request remote api failed: empty request!');
		$parms = func_get_args();
		unset($parms[0]);
		$parm_string = json_encode($parms);
		$parm_string = authcode($parm_string, self::key());
		$parm_string = bin2hex($parm_string);
		$res = fetch_url(self::API_ROOT."{$api_name}.php?sid=".self::id(), 0, 'parm='.$parm_string);
		if (!$res) throw new Exception('Request remote api failed: empty response!');
		$ret = json_decode($res);
		if (!$ret) throw new Exception('Request remote api failed: decode fail');
		return $ret;
	}
	public static id(){
		return 0;
	}
	public static key(){
		return 'Tieba Sign API - DEBUG';
	}
}
