<?php
define('DISABLE_CRON', true);
define('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';

if(!$uid){
	header('Location: member.php');
	exit();
}

if($_GET['action'] == 'baidu_login'){
	$status = cloud::ping();
	if($status == -1) echo <<<EOF
<script type="text/javascript">
var manual = '';
manual += '<p>很抱歉，我们的 API 系统暂时不可用。</p>';
manual += '<p>可能是服务器不稳定，也可能是 API 正在维护。</p><br>';
manual += '<p>你可以选择再试一次，也可以尝试手动绑定。</p>';
manual += '<p><a href="javascript:;" class="btn submit" onclick="$(\'#guide_page_2 form\').submit();">再试一次</a> &nbsp; <a href="javascript:;" class="btn red" onclick="$(\'#manual_bind\').toggleClass(\'hidden\');">手动绑定</a></p>';
manual += '<div id="manual_bind" class="hidden">';
manual += '<br>';
manual += '<p>请填写百度贴吧 Cookie:</p>';
manual += '<form method="post" action="index.php?action=update_cookie">';
manual += '<p>';
manual += '<input type="text" name="cookie" style="width: 60%" placeholder="请在此粘贴百度贴吧的 cookie" />';
manual += '<input type="submit" value="更新" />';
manual += '</p>';
manual += '</form>';
manual += '<br>';
manual += '<p>Cookie 获取工具:</p>';
manual += '<p>将本链接拖到收藏栏，在新页面点击收藏栏中的链接（推荐使用 Chrome 隐身窗口模式），按提示登陆wapp.baidu.com，登陆成功后，在该页面再次点击收藏栏中的链接即可复制cookies信息。</p>';
manual += '<p><a href="javascript:(function(){if(document.cookie.indexOf(\'BDUSS\')<0){alert(\'找不到BDUSS Cookie\\n请先登陆 http://wapp.baidu.com/\');location.href=\'http://wappass.baidu.com/passport/?login&u=http%3A%2F%2Fwapp.baidu.com%2F&ssid=&from=&uid=wapp_1375936328496_692&pu=&auth=&originid=2&mo_device=1&bd_page_type=1&tn=bdIndex&regtype=1&tpl=tb\';}else{prompt(\'您的 Cookie 信息如下:\', document.cookie);}})();" onclick="alert(\'请拖动到收藏夹后再使用\');return false;" class="btn">获取手机百度贴吧 Cookie</a></p>';
manual += '</div>';
try{ opener.$("#guide_page_2").hide(); opener.$("#guide_page_manual").html(manual); opener.$("#guide_page_manual").show(); window.close(); }catch(e){}
</script>
EOF;
	$parms = array($_POST['username'], $_POST['password'], $formhash);
	$parm_string = serialize($parms);
	$parm_string = authcode($parm_string, 'ENCODE', cloud::key());
	$parm_string = bin2hex($parm_string);
	header('Location: '.cloud::API_ROOT_HTTPS.'login.php?sid='.cloud::id().'&parm='.$parm_string);
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