<?php
if (!defined('IN_KKFRAME')) exit();

class cloud {
	const API_ROOT = 'http://api.ikk.me/v2/';
	const API_ROOT_HTTPS = 'https://api.ikk.me/v2/';
	public static function init(){
		list($id, $key) = self::_get_id_and_key();
		if (!$id || !$key) define('CLOUD_NOT_INITED', true);
	}
	public static function do_register(){
		global $siteurl;
		list($id, $key) = self::_get_id_and_key();
		if ($id && $key) return true;
		$ret = fetch_url(self::API_ROOT.'register.php', 0, 'url='.bin2hex(authcode($siteurl, 'ENCODE', 'CLOUD-REGISTER')));
		if(!$ret) return false;
		list($errno, $sid, $key) = explode("\t", $ret);
		if($errno != 1) throw new Exception('Fail to register in cloud system.');
		saveSetting('cloud', authcode("{$sid}\t{$key}", 'ENCODE', '-TiebaSignAPI-'));
	}
	public static function is_remote_disabled(){
		/* ... */
	}
	public static function request($api_name){
		if (!$api_name) throw new Exception('Request remote api failed: empty request!');
		$parms = func_get_args();
		unset($parms[0]);
		$parm_string = serialize($parms);
		$parm_string = authcode($parm_string, 'ENCODE', self::key());
		$parm_string = bin2hex($parm_string);
		$res = fetch_url(self::API_ROOT."{$api_name}.php?sid=".self::id(), 0, 'parm='.$parm_string);
		if (!$res) throw new Exception('Request remote api failed: empty response!');
		$ret = unserialize($res);
		if (!$ret) throw new Exception('Request remote api failed: decode fail');
		return $ret;
	}
	public static function request_public($api_name){
		if (!$api_name) throw new Exception('Request remote api failed: empty request!');
		$parms = func_get_args();
		unset($parms[0]);
		$parm_string = serialize($parms);
		$parm_string = authcode($parm_string, 'ENCODE', 'Tieba Sign API - DEBUG');
		$parm_string = bin2hex($parm_string);
		$res = fetch_url(self::API_ROOT."{$api_name}.php?sid=0", 0, 'parm='.$parm_string);
		if (!$res) throw new Exception('Request remote api failed: empty response!');
		$ret = unserialize($res);
		if (!$ret) throw new Exception('Request remote api failed: decode fail');
		return $ret;
	}
	public static function request_silent($api_name){
		if (!$api_name) throw new Exception('Request remote api failed: empty request!');
		$parms = func_get_args();
		unset($parms[0]);
		$parm_string = serialize($parms);
		$parm_string = authcode($parm_string, 'ENCODE', self::key());
		$parm_string = bin2hex($parm_string);
		$res = fetch_url(self::API_ROOT."{$api_name}.php?sid=".self::id(), 0, 'parm='.$parm_string);
		if (!$res) return -1;
		$ret = unserialize($res);
		if (!$ret) return -2;
		return $ret;
	}
	public static function ping(){
		$ret = self::request_silent('ping');
		return $ret;
	}
	public static function id(){
		list($id, $key) = self::_get_id_and_key();
		return $id;
	}
	public static function key(){
		list($id, $key) = self::_get_id_and_key();
		return $key;
	}
	private static function _get_id_and_key(){
		static $cached_request;
		if(isset($cached_request)) return $cached_request;
		$encrypted = getSetting('cloud');
		$cached_request = explode("\t", authcode($encrypted, 'DECODE', '-TiebaSignAPI-'));
		return $cached_request;
	}
}
