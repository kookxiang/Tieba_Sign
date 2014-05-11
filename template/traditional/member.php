<?php
if(!defined('IN_KKFRAME')) exit();
$extra_title = getSetting('extra_title');
$title = $extra_title ? "用户中心 - 贴吧签到助手 - {$extra_title}" : '用户中心 - 贴吧签到助手';
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>
<?php include template('widget/meta'); ?>
</head>
<body>
<div class="wrapper" id="page_login">
<div class="center-box">
<div class="side-bar">
<span class="icon"></span>
<ul>
<li id="menu_login" class="current">登录</li>
<?php if(!getSetting('block_register')) { ?>
<li id="menu_register">注册</li>
<?php } ?>
</ul>
</div>
<div class="main" id="content-login">
<?php include template('widget/login'); ?>
</div>
<?php if(!getSetting('block_register')){ ?>
<div class="main hidden" id="content-register">
<?php include template('widget/register'); ?>
</div>
<?php } ?>
<div class="main hidden" id="content-find_password">
<?php include template('widget/find_password'); ?>
</div>
</div>
<!-- 开发不易，跪求各位大大放俺一条生路 -->
<!-- 你可以在这加你自己的链接，但是麻烦保留下我的链接 Thanks -->
<p class="copyright">贴吧签到助手 <?php echo VERSION; ?> - Designed by <a href="http://www.ikk.me" target="_blank">kookxiang</a>. 2014 &copy; <a href="http://www.kookxiang.com" target="_blank">KK's Laboratory</a> (<a href="http://go.ikk.me/donate" target="_blank">赞助开发</a>)<?php if(getSetting('beian_no')) echo ' - <a href="http://www.miibeian.gov.cn/" target="_blank" rel="nofollow">'.getSetting('beian_no').'</a>'; ?></p>
<script src="<?php echo jquery_path(); ?>"></script>
<script src="./template/default/js/member.js?version=<?php echo VERSION; ?>"></script>
<?php HOOK::run('member_footer'); ?>
</div>
</body>
</html>