<?php
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
	header('Location: '.cloud::get_api_path().'login.php?sid='.cloud::id().'&parm='.$parm_string);
}elseif($_GET['action'] == 'register_cloud'){
	cloud::do_register();
}elseif($_GET['action'] == 'receive_cookie'){
	$_cookie = $_POST['cookie'] ? $_POST['cookie'] : $_GET['cookie'];
	if(!$_cookie) throw new Exception('Empty response!');
	if($_GET['formhash'] != $formhash) throw new Exception('Illegal request!');
	$cookie = authcode(pack('H*', $_cookie), 'DECODE', cloud::key());
	if(!$cookie) showmessage('非法调用！', './#baidu_bind', 1);
	if(!verify_cookie($cookie)) showmessage('无法登陆百度贴吧，请尝试重新绑定<form action="api.php?action=receive_cookie&formhash='.$formhash.'" method="post"><input type="hidden" name="cookie" value="'.$_cookie.'"></from><script type="text/javascript">setTimeout(function(){ document.forms[0].submit(); }, 2000);</script>');
	save_cookie($uid, $cookie);
	showmessage('绑定百度账号成功！<br>正在同步喜欢的贴吧...<script type="text/javascript" src="index.php?action=refresh_liked_tieba&formhash='.$formhash.'"></script><script type="text/javascript">try{ opener.$("#guide_page_2").hide(); opener.$("#guide_page_manual").hide(); opener.$("#guide_page_3").show(); window.close(); }catch(e){}</script>', './#baidu_bind', 1);
}

?>