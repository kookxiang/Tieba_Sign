<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h1>登录</h1>
<form method="post" action="member.php?action=login">
<div class="login-info">
<p>用户名：</p>
<p><input type="text" name="username" required tabindex="1" /></p>
<p>密码 (<a href="javascript:;" onclick="switch_tabs('find_password');" tabindex="0">找回密码</a>)：</p>
<p><input type="password" name="password" required tabindex="2" /></p>
<p>(此账号仅用于登陆代签系统，不同于百度通行证)</p>
<?php HOOK::run('login_form'); ?>
</div>
<p><input type="submit" value="登录" tabindex="3" /></p>
</form>