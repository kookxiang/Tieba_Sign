<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h1>找回密码</h1>
<form method="post" action="member.php?action=find_password">
<div class="login-info">
<p>用户名：</p>
<p><input type="text" name="username" required tabindex="1" /></p>
<p>邮箱：</p>
<p><input type="text" name="email" required tabindex="2" /></p>
</div>
<p><input type="submit" value="提交" tabindex="3" /></p>
</form>