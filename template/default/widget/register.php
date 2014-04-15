<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h1>注册</h1>
<form method="post" action="member.php?action=register">
<div class="login-info">
<p>用户名：</p>
<p><input type="text" name="<?php echo $form_username; ?>" required tabindex="1" /></p>
<p>密码：</p>
<p><input type="password" name="<?php echo $form_password; ?>" required tabindex="2" /></p>
<p>邮箱：</p>
<p><input type="text" name="<?php echo $form_email; ?>" required tabindex="3" /></p>
<?php
if($invite_code) echo '<p>邀请码：</p><p><input type="text" name="invite_code" required /></p>';
?>
<p>(此账号仅用于登陆代签系统，不同于百度通行证)</p>
<?php HOOK::run('register_form'); ?>
</div>
<p><input type="submit" value="注册" tabindex="4" /></p>
</form>