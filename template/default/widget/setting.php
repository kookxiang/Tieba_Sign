<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h2>设置</h2>
<form method="post" action="index.php?action=update_setting" id="setting_form" onsubmit="return post_win(this.action, this.id)">
<input type="hidden" name="formhash" value="<?php echo $formhash; ?>">
<p>签到方式：</p>
<p><label><input type="radio" name="sign_method" id="sign_method_3" value="3" checked readonly /> V3.0 (模拟客户端签到)</label></p>
<p>附加签到：</p>
<p><label><input type="checkbox" disabled name="zhidao_sign" id="zhidao_sign" value="1" /> 自动签到百度知道</label></p>
<p><label><input type="checkbox" disabled name="wenku_sign" id="wenku_sign" value="1" /> 自动签到百度文库</label></p>
<p>报告设置：</p>
<p><label><input type="radio" checked readonly /> 发送简要报告</label></p>
<p>Golang 后端暂时不支持修改邮件接收设置，如需退订请联系管理员。</p>
<p><input type="submit" value="保存设置" /></p>
</form>
<?php HOOK::run('user_setting'); ?>
<br>
<p>签到测试：</p>
<p>随机选取一个贴吧，进行一次签到测试，检查你的设置有没有问题</p>
<p>测个 JB，现在后端换 Go 语言了，测球</p>
