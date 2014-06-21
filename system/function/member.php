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
	HOOK::run('delete_user', true, $uid);
}

function _do_login($uid){
	global $cookiever;
	$user = DB::fetch_first("SELECT * FROM member WHERE uid='{$uid}'");
	$password_hash = substr(md5($user['password']), 8, 8);
	$login_exp = TIMESTAMP + 900;
	dsetcookie('token', authcode("{$cookiever}\t{$uid}\t{$user[username]}\t{$login_exp}\t{$password_hash}", 'ENCODE'));
	HOOK::run('login_user', true, $user);
}

function _do_register($username, $password, $email){
	$user = array(
		'username' => $username,
		'password' => 'FAKE_PASSWORD',
		'email' => $email,
	);
	$uid = DB::insert('member', $user);
	$user['uid'] = $uid;
	$password = Widget_Password::encrypt($user, $password);
	DB::query("UPDATE member SET password='{$password}' WHERE uid='{$uid}'");
	DB::insert('member_setting', array('uid' => $uid, 'cookie' => ''));
	HOOK::run('register_user', true, $user);
	CACHE::update('username');
	CACHE::save('user_setting_'.$uid, '');
	return $uid;
}