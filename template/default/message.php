<?php
if(!defined('IN_KKFRAME')) exit();
?>
<!DOCTYPE html>
<html>
<meta charset=utf-8>
<title>系统消息</title>
<meta name=viewport content="initial-scale=1, minimum-scale=1, width=device-width">
<style>
*{margin:0;padding:0}
html,code{font:15px/22px arial,sans-serif}
html{background:#fff;color:#222;padding:15px}
body{margin:7% auto 0;max-width:390px;min-height:220px;padding:75px 0 15px}
* > body{background:url(template/default/style/msg_bg.png) 100% 5px no-repeat;padding-right:205px}
p{margin:11px 0 22px;overflow:hidden}
ins, ins a{color:#777;text-decoration:none;font-size:10px;}
a img{border:0}
@media screen and (max-width:772px){body{background:none;margin-top:0;max-width:none;padding-right:0}}
</style>
<body>
<p><b>系统消息 - 贴吧签到助手</b></p>
<?php
echo "<p>{$msg}</p>";
if($redirect)
	echo "<ins><a href=\"{$redirect}\">如果您的浏览器没有自动跳转，请点击这里</a></ins><meta http-equiv=\"refresh\" content=\"{$delay};url={$redirect}\" />";
?>
</body>
</html>