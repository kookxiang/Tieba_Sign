<?php
if(!defined('IN_KKFRAME')) exit();
$extra_title = getSetting('extra_title');
$title = $extra_title ? "贴吧签到助手 - {$extra_title}" : '贴吧签到助手';
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>
<?php include template('widget/meta'); ?>
</head>
<body>
<div id="append_parent"><div class="cover hidden"></div><div class="loading-icon"><img src="./template/default/style/loading.gif" /> 载入中...</div></div>
<div class="wrapper" id="page_index">
<?php include template('widget/header'); ?>
<div class="sidebar">
<?php include template('widget/sidebar'); ?>
</div>
<div class="main-content">
<div id="content-guide" class="hidden">
<?php include template('widget/guide'); ?>
</div>
<div id="content-liked_tieba" class="hidden">
<?php include template('widget/liked_tieba'); ?>
</div>
<div id="content-sign_log">
<?php include template('widget/sign_log'); ?>
</div>
<div id="content-setting" class="hidden">
<?php include template('widget/setting'); ?>
</div>
<div id="content-baidu_bind" class="hidden">
<?php include template('widget/bind_status'); ?>
</div>
<?php HOOK::page_contents(); ?>
<!-- 开发不易，跪求各位大大放俺一条生路 -->
<!-- 你可以在这加你自己的链接，但是麻烦保留下我的链接 Thanks -->
<p class="copyright"><span class="mobile_hidden">贴吧签到助手 - Designed</span> by <a href="http://www.ikk.me" target="_blank">kookxiang</a>. 2014 &copy; <a href="http://www.kookxiang.com" target="_blank">KK's Laboratory</a> - <a href="http://go.ikk.me/donate" target="_blank">赞助开发</a><br>
<?php if(getSetting('beian_no')) echo '<a href="http://www.miibeian.gov.cn/" target="_blank" rel="nofollow">'.getSetting('beian_no').'</a> - '; ?><?php HOOK::run('page_footer'); ?></p>
</div>
<?php include template('widget/footer'); ?>
</body>
</html>