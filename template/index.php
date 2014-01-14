<?php
if(!defined('IN_KKFRAME')) exit();
?>
<!DOCTYPE html>
<html>
<head>
<title>贴吧签到助手</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="HandheldFriendly" content="true" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="author" content="kookxiang" />
<meta name="copyright" content="KK's Laboratory" />
<link rel="shortcut icon" href="favicon.ico" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="renderer" content="webkit">
<link rel="stylesheet" href="./style/main.css?version=<?php echo VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="./style/custom.css" type="text/css" />
</head>
<body>
<div class="wrapper" id="page_index">
<div id="append_parent"><div class="loading-icon"><img src="style/loading.gif" /> 载入中...</div></div>
<div class="main-box clearfix">
<h1>贴吧签到助手</h1>
<div class="avatar"><?php echo $username; echo $_COOKIE["avatar_{$uid}"] ? '<img id="avatar_img" src="'.$_COOKIE["avatar_{$uid}"].'">' : '<img id="avatar_img" class="hidden" src="style/member.png">'; ?></div>
<ul class="menu hidden" id="member-menu">
<li id="menu_password"><a href="javascript:;">修改密码</a></li>
<?php
if(getSetting('account_switch')){
	foreach ($users as $_uid => $username){
		echo '<li class="menu_switch_user"><span class="del" href="member.php?action=unbind_user&uid='.$_uid.'&formhash='.$formhash.'">x</span><a href="member.php?action=switch&uid='.$_uid.'&formhash='.$formhash.'">切换至: '.$username.'</a></li>';
	}
	echo '<li id="menu_adduser"><a href="#user-new">关联其他帐号</a></li>';
}
?>
<li id="menu_logout"><a href="member.php?action=logout&hash=<?php echo $formhash; ?>">退出登录</a></li>
</ul>
<div class="menubtn"><p>-</p><p>-</p><p>-</p></div>
<div class="main-wrapper">
<div class="sidebar">
<ul id="menu" class="menu">
<li id="menu_guide"><a href="#guide">配置向导</a></li>
<li id="menu_sign_log"><a href="#sign_log">签到记录</a></li>
<li id="menu_liked_tieba"><a href="#liked_tieba">我喜欢的贴吧</a></li>
<li id="menu_baidu_bind"><a href="#baidu_bind">百度账号绑定</a></li>
<li id="menu_setting"><a href="#setting">设置</a></li>
<?php HOOK::page_menu(); ?>
<?php if(is_admin($uid)) echo '<li id="menu_updater"><a href="admin.php#updater">检查更新</a></li><li id="menu_admincp"><a href="admin.php">管理面板</a></li>'; ?>
</ul>
</div>
<div class="main-content">
<div id="content-guide" class="hidden">
</div>
<div id="content-liked_tieba" class="hidden">
<h2>我喜欢的贴吧</h2>
<p>如果此处显示的贴吧有缺失，请<a href="index.php?action=refresh_liked_tieba" onclick="return msg_redirect_action(this.href+'&formhash='+formhash)">点此刷新喜欢的贴吧</a>.</p>
<table>
<thead><tr><td style="width: 40px">#</td><td>贴吧</td><td style="width: 65px">忽略签到</td></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="content-sign_log">
<h2>签到记录</h2>
<span id="page-flip" class="float-right"></span>
<p id="sign-stat"></p>
<table>
<thead><tr><td style="width: 40px">#</td><td>贴吧</td><td class="mobile_min">状态</td><td class="mobile_min">经验</td></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="content-setting" class="hidden">
<h2>设置</h2>
<form method="post" action="index.php?action=update_setting" id="setting_form" onsubmit="return post_win(this.action, this.id)">
<input type="hidden" name="formhash" value="<?php echo $formhash; ?>">
<p>签到方式：</p>
<p><label><input type="radio" name="sign_method" id="sign_method_3" value="3" checked readonly /> V3.0 (模拟客户端签到)</label></p>
<p>附加签到：</p>
<p><label><input type="checkbox" disabled name="zhidao_sign" id="zhidao_sign" value="1" /> 自动签到百度知道</label></p>
<p><label><input type="checkbox" disabled name="wenku_sign" id="wenku_sign" value="1" /> 自动签到百度文库</label></p>
<p>报告设置：</p>
<p><label><input type="checkbox" checked disabled name="error_mail" id="error_mail" value="1" /> 当天有无法签到的贴吧时给我发送邮件</label></p>
<p><label><input type="checkbox" disabled name="send_mail" id="send_mail" value="1" /> 每日发送一封签到报告邮件</label></p>
<p><input type="submit" value="保存设置" /></p>
</form>
<?php HOOK::run('user_setting'); ?>
<br>
<p>签到测试：</p>
<p>随机选取一个贴吧，进行一次签到测试，检查你的设置有没有问题</p>
<p><a href="index.php?action=test_sign&formhash=<?php echo $formhash; ?>" class="btn" onclick="return msg_redirect_action(this.href)">测试签到</a></p>
</div>
<div id="content-baidu_bind" class="hidden">
<h2>百度账号绑定</h2>
<div class="tab tab-binded hidden">
<p>您的百度账号绑定正常。</p>
<br>
<div class="baidu_account"></div>
<br>
<p><a href="index.php?action=clear_cookie&formhash=<?php echo $formhash; ?>" id="unbind_btn" class="btn">解除绑定</a> &nbsp; (解除绑定后自动签到将停止)</p>
</div>
<div class="tab tab-bind">
<p>您还没有绑定百度账号！</p>
<br>
<p>只有绑定百度账号之后程序才能自动进行签到。</p>
<p>您可以使用百度通行证登陆，或是手动填写 Cookie 进行绑定。</p>
<br>
<p><a href="https://api.ikk.me/baidu_login.php?callback=<?php echo rawurlencode($siteurl)."&formhash={$formhash}&ver=".VERSION; ?>" class="btn" target="_blank">点击此处登陆百度通行证</a> &nbsp; <a href="javascript:;" class="btn" id="show_cookie_setting">手动绑定</a></p>
</div>
<div class="tab-cookie hidden">
<br>
<h2>手动绑定百度账号</h2>
<p>请填写百度贴吧 Cookie:</p>
<form method="post" action="index.php?action=update_cookie">
<p>
<input type="text" name="cookie" style="width: 60%" placeholder="请在此粘贴百度贴吧的 cookie" />
<input type="submit" value="更新" />
</p>
</form>
<br>
<p>Cookie 获取工具:</p>
<p>将本链接拖到收藏栏，在新页面点击收藏栏中的链接（推荐使用 Chrome 隐身窗口模式），按提示登陆wapp.baidu.com，登陆成功后，在该页面再次点击收藏栏中的链接即可复制cookies信息。</p>
<p><a href="javascript:(function(){if(document.cookie.indexOf('BDUSS')<0){alert('找不到BDUSS Cookie\n请先登陆 http://wapp.baidu.com/');location.href='http://wappass.baidu.com/passport/?login&u=http%3A%2F%2Fwapp.baidu.com%2F&ssid=&from=&uid=wapp_1375936328496_692&pu=&auth=&originid=2&mo_device=1&bd_page_type=1&tn=bdIndex&regtype=1&tpl=tb';}else{prompt('您的 Cookie 信息如下:', document.cookie);}})();" onclick="alert('请拖动到收藏夹');return false;" class="btn">获取手机百度贴吧 Cookie</a></p>
</div>
</div>
<?php HOOK::page_contents(); ?>
<p>贴吧签到助手 - Designed by <a href="http://www.ikk.me" target="_blank">kookxiang</a>. 2014 &copy; <a href="http://www.kookxiang.com" target="_blank">KK's Laboratory</a> - <a href="https://me.alipay.com/kookxiang" target="_blank">赞助开发</a></p>
</div>
</div>
</div>
<p class="copyright"><?php if(getSetting('beian_no')) echo '<a href="http://www.miibeian.gov.cn/" target="_blank" rel="nofollow">'.getSetting('beian_no').'</a> - '; ?><?php HOOK::run('page_footer'); ?></p>
</div>
<script src="<?php echo jquery_path(); ?>"></script>
<script type="text/javascript">var formhash = '<?php echo $formhash; ?>';var version = '<?php echo VERSION; ?>';</script>
<script src="system/js/kk_dropdown.js?version=<?php echo VERSION; ?>"></script>
<script src="system/js/main.js?version=<?php echo VERSION; ?>"></script>
<script src="system/js/fwin.js?version=<?php echo VERSION; ?>"></script>
<script type="text/javascript">defered_js.push('//api.ikk.me/guide.js');</script>
<?php
HOOK::run('page_footer_js');
if(getSetting('stat_code')) echo '<div class="hidden">'.getSetting('stat_code').'</div>';
?>
</body>
</html>