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
html{background:#fff;color:#222;padding:15px}
body{margin:20% auto 0;min-height:220px;padding:30px 0 15px}
p{margin:11px 0 22px;overflow:hidden}
ins, ins a{color:#777;text-decoration:none;font-size:10px;}
a img{border:0;margin:0 auto;}
</style>
<body>
<?php
echo "<p>{$msg}</p>";
if($redirect)
	echo "<ins><a href=\"{$redirect}\">如果您的浏览器没有自动跳转，请点击这里</a></ins><meta http-equiv=\"refresh\" content=\"{$delay};url={$redirect}\" />";
?>
</body>
</html>