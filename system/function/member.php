<?php
if(!defined('IN_KKFRAME')) exit();

function _delete_user($uid){
	if(!$uid) return;
	if(!is_array($uid)) $uid = array($uid);
	$uid = implode("', '", $uid);
	DB::query("DELETE FROM member WHERE uid IN ('{$uid}')");
	DB::query("DELETE FROM member_setting WHERE uid IN ('{$uid}')");
	DB::query("DELETE FROM my_tieba WHERE uid IN ('{$uid}')");
	DB::query("DELETE FROM sign_log WHERE uid IN ('{$uid}')");
	HOOK::run('delete_user', $uid);
}

function _do_login($uid){
	global $cookiever;
	$user = DB::fetch_first("SELECT * FROM member WHERE uid='{$uid}'");
	$password_hash = substr(md5($user['password']), 8, 8);
	$login_exp = TIMESTAMP + 900;
	dsetcookie('token', authcode("{$cookiever}\t{$uid}\t{$user[username]}\t{$login_exp}\t{$password_hash}", 'ENCODE'));
}

function _do_register($username,$password,$email){
	$uid = DB::insert('member', array(
				'username' => $username,
				'password' => $password,
				'email' => $email,
			));
	DB::insert('member_setting', array('uid' => $uid, 'cookie' => ''));
	CACHE::update('username');
	CACHE::save('user_setting_'.$uid, '');
	return $uid;
}