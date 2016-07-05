<?php
if (!defined('IN_KKFRAME')) exit();
class Widget_Password {
	const ENCRYPT_TYPE_DEFAULT = 0;
	const ENCRYPT_TYPE_ENHANCE = 1;
	const ENCRYPT_TYPE_3MD5 = 2;
	function verify($user, $password){
		list($user_password, $encrypt_type) = explode('T', $user['password']);
		if($encrypt_type == self::ENCRYPT_TYPE_DEFAULT){
			return $user_password == md5(ENCRYPT_KEY.md5($password).ENCRYPT_KEY);
		}elseif($encrypt_type == self::ENCRYPT_TYPE_ENHANCE){
			$salt = substr(md5($user['uid'].$user['username'].ENCRYPT_KEY), 8, 16);
			return $user_password == substr(md5(md5($password).$salt), 0, 30);
		}elseif($encrypt_type == self::ENCRYPT_TYPE_3MD5){
			return $user_password === md5(md5(md5($password)));
		}else{
			return password_verify($password, $user['password']);
		}
	}
	function encrypt($user, $password){
		return password_hash($password, PASSWORD_BCRYPT);
	}
}
