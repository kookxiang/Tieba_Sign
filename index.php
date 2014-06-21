<?php
require_once './system/common.inc.php';

if(!$uid){
	header('Location: member.php');
	exit();
}elseif($_GET['action']){
	switch($_GET['action']){
		case 'skip_tieba':
			if($_GET['formhash'] != $formhash) break;
			$tid = intval($_GET['tid']);
			$skiped = DB::result_first("SELECT skiped FROM my_tieba WHERE uid='{$uid}' AND tid='{$tid}'");
			$skiped = $skiped ? 0 : 1;
			$date = date('Ymd', TIMESTAMP+900);
			if($skiped == 1){
				DB::query("UPDATE sign_log SET `status`='-2' WHERE uid='{$uid}' AND tid='{$tid}' AND date='{$date}' AND `status` < 2");
			}else{
				DB::query("UPDATE sign_log SET `status`='0' WHERE uid='{$uid}' AND tid='{$tid}' AND date='{$date}' AND `status` < 2");
			}
			DB::query("UPDATE my_tieba SET skiped='{$skiped}' WHERE uid='{$uid}' AND tid='{$tid}'");
			if(!DB::affected_rows()) showmessage('发生未知错误: 无法修改贴吧设置');
			showmessage('修改签到设置成功！');
		case 'test_sign':
			if($_GET['formhash'] != $formhash) break;
			$tieba = DB::fetch_first("SELECT * FROM my_tieba WHERE uid='{$uid}' ORDER BY RAND() LIMIT 0,1");
			if(!$tieba) showmessage('没有喜欢的贴吧，请先刷新喜欢的贴吧列表', './#liked_tieba');
			$setting = get_setting($uid);
			list($status, $result, $exp) = client_sign($uid, $tieba);
			$status = $status==2 ? '签到成功' : '签到失败';
			showmessage("<p>测试贴吧：{$tieba[name]}</p><p>测试结果：{$status}</p><p>详细信息：{$result}</p>", './#setting', 1);
			break;
		case 'clear_cookie':
			if($_GET['formhash'] != $formhash) break;
			DB::query("UPDATE member_setting SET cookie='' WHERE uid='{$uid}'");
			DB::query("DELETE FROM my_tieba WHERE uid='{$uid}'");
			DB::query("DELETE FROM sign_log WHERE uid='{$uid}'");
			showmessage('已经解除百度账号绑定<br>您可以稍后重新进行绑定', './#baidu_bind#', 1);
		case 'update_cookie':
			if(!$_POST['cookie']) break;
			$cookie = daddslashes($_POST['cookie']);
			if(!preg_match('/BDUSS=(.+?)/', $cookie)) showmessage('Cookie 信息不完整，请尝试重新获取', './#baidu_bind', 1);
			if(!preg_match('/BAIDUID=(.+?)/', $cookie)) showmessage('Cookie 信息不完整，请尝试重新获取', './#baidu_bind', 1);
			if(!verify_cookie($cookie)) showmessage('无法登陆百度贴吧，请检查 Cookie 是否填写正确', './#baidu_bind', 1);
			$cookie = daddslashes($cookie);
			save_cookie($uid, $cookie);
			showmessage('您的 Cookie 信息已经更新<script type="text/javascript" src="?action=refresh_liked_tieba&formhash='.$formhash.'"></script>', './#baidu_bind', 1);
			break;
		case 'update_setting':
			if($_POST['formhash'] != $formhash) break;
			DB::update('member_setting', array(
				'error_mail' => $_POST['error_mail'] ? 1 : 0,
				'send_mail' => $_POST['send_mail'] ? 1 : 0,
				'zhidao_sign' => $_POST['zhidao_sign'] ? 1 : 0,
				'wenku_sign' => $_POST['wenku_sign'] ? 1 : 0,
				), "uid='{$uid}'");
			CACHE::save('user_setting_'.$uid, '');
			showmessage('设置已经保存', './#setting', 1);
			break;
		case 'change_password':
			if($_POST['formhash'] != $formhash) break;
			$user = DB::fetch_first("SELECT * FROM member WHERE uid='{$uid}'");
			if(!$_POST['old_password']) showmessage('请输入旧密码', './#setting', 1);
			if(!$_POST['new_password']) showmessage('请输入新密码', './#setting', 1);
			if($_POST['new_password'] != $_POST['new_password2']) showmessage('两次输入的新密码不一样，请检查', './#setting', 1);
			if(!Widget_Password::verify($user, $_POST['old_password'])) showmessage('旧密码错误！请检查输入', './#setting', 1);
			$newpassword = Widget_Password::encrypt($user, $_POST['new_password']);
			DB::update('member', array('password' => $newpassword), "uid='{$uid}'");
			HOOK::run('change_password', true, $uid);
			showmessage('您的密码已经更新', './#setting', 1);
			break;
		case 'reset_failure':
			if($formhash != $_GET['formhash']) showmessage('请稍候...', '?action=reset_failure&formhash='.$formhash, 0);
			$date = date('Ymd');
			DB::query("UPDATE sign_log SET status='0', retry='0' WHERE uid='{$uid}' AND date='{$date}' AND status<0");
			showmessage('已经重置失败状态，稍后系统将自动重试', './#signlog', 1);
			break;
		case 'refresh_liked_tieba':
			if($formhash != $_GET['formhash']) showmessage('刷新中，请稍候...', '?action=refresh_liked_tieba&formhash='.$formhash, 0);
			list($insert, $deleted) = update_liked_tieba($uid);
			showmessage("喜欢的贴吧列表已经更新,<br>新增{$insert}个贴吧, 删除{$deleted}个贴吧", './#liked_tieba', 1);
			break;
	}
	header('Location: ./');
	exit();
}

if($_GET['ignore_update']){
	dsetcookie('ignore_update', '1', 7200);
	exit();
}elseif(is_admin($uid) && !$_COOKIE['ignore_update']){
	if(getSetting('new_version')) define('NEW_VERSION', true);
}
if(is_admin($uid) && getSetting('next_cron') < TIMESTAMP - 7200) define('CRON_ERROR', true);

if(getSetting('account_switch')){
	// Multi User Support
	$query = DB::query("SELECT * FROM member_bind WHERE uid='{$uid}'");
	$users = array();
	while($result = DB::fetch($query)){
		$users[ $result['_uid'] ] = $result['username'];
	}
}

include template('index');