<?php
require_once './system/common.inc.php';
$invite_code = getSetting('invite_code');
if($_GET['action'] == 'logout' && $_GET['hash']==$formhash){
	dsetcookie('token', '');
	$_COOKIE['token'] = '';
	showmessage('您已经退出登录了！', dreferer(), 1);
}elseif($uid && $_GET['action'] == 'unbind_user'){
	if($_GET['formhash'] != $formhash) showmessage('来源不可信，请重试', './');
	$_uid = intval($_GET['uid']);
	$user = DB::fetch_first("SELECT * FROM member_bind WHERE uid='{$uid}' AND _uid='{$_uid}'");
	if(!$user) showmessage('你并没有绑定该账号', './');
	DB::query("DELETE FROM member_bind WHERE uid='{$uid}' AND _uid='{$_uid}'");
	DB::query("DELETE FROM member_bind WHERE uid='{$_uid}' AND _uid='{$uid}'");
	showmessage("成功解除与 {$user['username']} 的绑定！", './');
}elseif($uid && $_GET['action'] == 'bind_user'){
	if($_POST['formhash'] != $formhash) showmessage('来源不可信，请重试', './');
	if(!$_POST['username']){
		showmessage('请输入用户名', './#');
	}elseif(!$_POST['password']){
		showmessage('请输入密码', './#');
	}
	$_username = daddslashes($_POST['username']);
	if($_username == $username) showmessage('请输入其他账户的信息', './#');
	if(strlen($_username) > 24) showmessage('用户名过长，请修改', dreferer(), 5);
	$user = DB::fetch_first("SELECT * FROM member WHERE username='{$_username}'");
	$userid = $user['uid'];
	$verified = Widget_Password::verify($user, $_POST['password']);
	if($verified){
		$exists = DB::result_first("SELECT _uid FROM member_bind WHERE uid='{$uid}' AND _uid='{$userid}'");
		if($exists) showmessage('您此前已经绑定过此帐号', './#');
		DB::insert('member_bind', array(
			'uid' => $uid,
			'_uid' => $userid,
			'username' => $user['username'],
		));
		$exists = DB::result_first("SELECT uid FROM member_bind WHERE _uid='{$uid}' AND uid='{$userid}'");
		if(!$exists){
			$username = DB::result_first("SELECT username FROM member WHERE uid='{$uid}'");
			DB::insert('member_bind', array(
				'uid' => $userid,
				'_uid' => $uid,
				'username' => $username,
			));
		}
		showmessage("您已经成功绑定用户“{$user[username]}”", './');
	}else{
		showmessage('用户名/密码不正确！', './#');
	}
}elseif($uid && $_GET['action'] == 'switch'){
	if($_GET['formhash'] != $formhash) showmessage('来源不可信，请重试', './');
	$target_uid = intval($_GET['uid']);
	$uid = DB::result_first("SELECT _uid FROM member_bind WHERE uid='{$uid}' AND _uid='{$target_uid}'");
	if(!$uid) showmessage('您尚未绑定该账号，无法进行切换', './');
	$username = get_username($uid);
	do_login($uid);
	showmessage("您已经成功切换至 {$username}！", dreferer(), 1);
}elseif($uid){
	showmessage('您已经登录了~', dreferer(), 1);
}elseif($_GET['action'] == 'find_password'){
	if($_GET['token']){
		$str = authcode($_GET['token'], 'DECODE');
		if(!$str) showmessage('链接有误，请重新获取', './');
		list($uid, $exptime, $password, $random) = explode("\t", $str);
		if($exptime < TIMESTAMP) showmessage('链接已过期，请重新获取', './');
		$user = DB::fetch_first("SELECT * FROM member WHERE uid='{$uid}' AND password='{$password}'");
		if(!$user) showmessage('链接已经失效，请重新获取', './');
		$new_password = random(10);
		$newpassword = Widget_Password::encrypt($user, $new_password);
		DB::update('member', array('password' => $newpassword), "uid='{$uid}'");
		showmessage("您的密码已经重置为：<br>{$new_password}<br><br>请使用新密码登录并修改密码。");
	}elseif($_POST['username'] && $_POST['email']){
		$username = daddslashes($_POST['username']);
		$email = daddslashes($_POST['email']);
		$user = DB::fetch_first("SELECT * FROM member WHERE username='{$username}' AND email='{$email}'");
		if(!$user) showmessage('用户名 / 邮箱有误', './');
		$info = array(
			$user['uid'],			// UID
			TIMESTAMP + 3600,		// Token 过期时间
			$user['password'],		// 当前密码
			random(32),				// 随机字符
		);
		$token = urlencode(authcode(implode("\t", $info), 'ENCODE'));
		$link = "{$siteurl}member.php?action=find_password&token={$token}";
		$message = <<<EOF
<p>我们已经收到您的找回密码申请，请您点击下方的链接重新设置密码：</p>
<blockquote><a href="{$link}">{$link}</a></blockquote>
<p>（注：请在一小时内点击上面的链接，我们将向您提供新的密码）</p>
<br>
<p>如果您没有要求重置密码却收到本邮件，请及时删除此邮件以确保账户安全。</p>
EOF;
		DB::insert('mail_queue', array(
			'to' => $user['email'],
			'subject' => "贴吧签到助手 - 密码找回",
			'content' => $message,
			));
		saveSetting('mail_queue', 1);
		showmessage('邮件发送成功，请到邮箱查收', './');
	}
	header('Location: member.php');
	exit();
}elseif($_GET['action'] == 'register'){
	if(getSetting('block_register')) showmessage('抱歉，当前站点禁止新用户注册', 'member.php');
	$count = DB::result_first('SELECT COUNT(*) FROM member');
	if($_POST && strexists($_SERVER['HTTP_REFERER'], 'member.php')){
		list($time, $hash, $member_count) = explode("\t", authcode($_COOKIE['key'], 'DECODE'));
		if(getSetting('register_check') && $time > TIMESTAMP - 5 || $time < TIMESTAMP - 300) $_POST = array();
		if(getSetting('register_limit') && $member_count != $count) showmessage('当前注册人数过多，请您稍后再试', 'member.php');
		if($count > 1000) showmessage('超过当前站点最大用户数量上限，无法注册', 'member.php');
		$_POST['username'] = $_POST['password'] = $_POST['email'] = null;
		foreach($_POST as $key => $value){
			$key = authcode($key, 'DECODE', $hash);
			if($key == 'username'){
				$_POST['username'] = $value;
			}elseif($key == 'password'){
				$_POST['password'] = $value;
			}elseif($key == 'email'){
				$_POST['email'] = $value;
			}
		}
		if(!$_POST['username']){
			showmessage('请输入用户名', 'member.php');
		}elseif(!$_POST['password']){
			showmessage('请输入密码', 'member.php');
		}elseif(!$_POST['email']){
			showmessage('请输入您的邮箱', 'member.php');
		}else{
			if($invite_code && $_POST['invite_code'] != $invite_code) showmessage('邀请码有误', 'member.php');
			$username = daddslashes($_POST['username']);
			$email = daddslashes($_POST['email']);
			if(!is_email($email)) showmessage('邮箱格式不正确，请修改', dreferer(), 5);
			if(!$username || !$_POST['password'] || !$email) showmessage('您输入的信息不完整', 'member.php');
			if(preg_match('/[<>\'\\"]/i', $username)) showmessage('用户名中有被禁止使用的关键字', 'member.php');
			if(strlen($username) < 6) showmessage('用户名至少要6个字符(即2个中文 或 6个英文)，请修改', dreferer(), 5);
			if(strlen($username) > 24) showmessage('用户名过长，请修改', dreferer(), 5);
			$un = strtolower($username);
			if(strexists($un, 'admin') || strexists($un, 'guanli')) showmessage('用户名不和谐，请修改', dreferer(), 5);
			$user = DB::fetch_first("SELECT * FROM member WHERE username='{$username}'");
			if($user) showmessage('用户名已经存在', 'member.php');
			HOOK::run('before_register');
			$uid = do_register($username, $_POST['password'], $email);
			do_login($uid);
			HOOK::run('register_finish', $uid);
			showmessage("注册成功，您的用户名是 <b>{$username}</b> 记住了哦~！", dreferer(), 3);
		}
	}
	header('Location: member.php');
	exit();
}elseif($_POST){
	if($_POST['username'] && $_POST['password']){
		$username = daddslashes($_POST['username']);
		$un = strtolower($username);
		if(strlen($username) > 24) showmessage('用户名过长，请修改', dreferer(), 5);
		$user = DB::fetch_first("SELECT * FROM member WHERE username='{$username}'");
		$verified = Widget_Password::verify($user, $_POST['password']);
		if($verified) {
			$login_exp = TIMESTAMP + 3600;
			do_login($user['uid']);
			$username = $user['username'];
			showmessage("欢迎回来，{$username}！", dreferer(), 1);
		}else{
			showmessage('对不起，您的用户名或密码错误，无法登录.', 'member.php', 3);
		}
	}
}
$count = DB::result_first('SELECT COUNT(*) FROM member');
$hash = random(6);
$time = TIMESTAMP;
dsetcookie('key', authcode("{$time}\t{$hash}\t{$count}", 'ENCODE'));
$form_username = authcode('username', 'ENCODE', $hash);
$form_password = authcode('password', 'ENCODE', $hash);
$form_email = authcode('email', 'ENCODE', $hash);
include template('member');