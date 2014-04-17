<?php
define('DISABLE_CRON', true);
define('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';

if(!$uid){
	header('Location: member.php');
	exit();
}

if($_GET['action'] == 'baidu_login'){
	$parms = array($_POST['username'], $_POST['password'], $formhash);
	$parm_string = serialize($parms);
	$parm_string = authcode($parm_string, 'ENCODE', cloud::key());
	$parm_string = bin2hex($parm_string);
	$api_path = getSetting('use_sae_api') ? 'http://sae.api.ikk.me/' : cloud::API_ROOT_HTTPS;
	header('Location: '.$api_path.'login.php?sid='.cloud::id().'&parm='.$parm_string);
}elseif($_GET['action'] == 'register_cloud'){
	cloud::do_register();
}elseif($_GET['action'] == 'receive_cookie'){
	$cookie = $_POST['cookie'] ? $_POST['cookie'] : $_GET['cookie'];
	if(!$cookie) throw new Exception('Empty response!');
	if($_GET['formhash'] != $formhash) throw new Exception('Illegal request!');
	$cookie = authcode(pack('H*', $cookie), 'DECODE', cloud::key());
	if(!$cookie) showmessage('非法调用！', './#baidu_bind', 1);
	if(!verify_cookie($cookie)) showmessage('无法登陆百度贴吧，请尝试重新绑定', './#baidu_bind', 1);
	save_cookie($uid, $cookie);
	showmessage('绑定百度账号成功！<br>正在同步喜欢的贴吧...<script type="text/javascript" src="index.php?action=refresh_liked_tieba&formhash='.$formhash.'"></script><script type="text/javascript">try{ opener.$("#guide_page_2").hide(); opener.$("#guide_page_manual").hide(); opener.$("#guide_page_3").show(); window.close(); }catch(e){}</script>', './#baidu_bind', 1);
}

?>