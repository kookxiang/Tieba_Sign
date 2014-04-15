<?php
if(!defined('IN_KKFRAME')) exit();
?>
<ul id="menu" class="menu">
<li id="menu_guide"><a href="#guide">配置向导</a></li>
<li id="menu_sign_log"><a href="#sign_log">签到记录</a></li>
<li id="menu_liked_tieba"><a href="#liked_tieba">我喜欢的贴吧</a></li>
<li id="menu_baidu_bind"><a href="#baidu_bind">百度账号绑定</a></li>
<li id="menu_setting"><a href="#setting">设置</a></li>
<?php HOOK::page_menu(); ?>
<?php if(is_admin($uid)) echo '<li id="menu_updater"><a href="admin.php#updater">检查更新</a></li><li id="menu_admincp"><a href="admin.php">管理面板</a></li>'; ?>
</ul>