<?php
if(!defined('IN_ADMINCP')) exit();
?>
<!DOCTYPE html>
<html>
<head>
<title>管理中心 - 贴吧签到助手</title>
<?php include template('widget/meta'); ?>
</head>
<body>
<div id="append_parent"><div class="cover hidden"></div><div class="loading-icon"><img src="./template/default/style/loading.gif" /> 载入中...</div></div>
<div class="wrapper" id="page_index">
<h1>贴吧签到助手 - 管理中心</h1>
<div class="menubtn"><p>-</p><p>-</p><p>-</p></div>
<div class="sidebar">
<ul id="menu" class="menu">
<li id="menu_user"><a href="#user">用户管理</a></li>
<li id="menu_stat"><a href="#stat">用户签到统计</a></li>
<li id="menu_plugin"><a href="#plugin">插件管理</a></li>
<li id="menu_template"><a href="#template">模板管理</a></li>
<li id="menu_setting"><a href="#setting">系统设置</a></li>
<li id="menu_mail"><a href="#mail">邮件群发</a></li>
<li id="menu_cron"><a href="#cron">计划任务</a></li>
<li id="menu_updater"><a href="#updater">检查更新</a></li>
<li><a href="./">返回前台</a></li>
</ul>
</div>
<div class="main-content">
<div id="content-loader">
<h2>正在加载 jQuery 组件...</h2>
<p>首次加载需要较长时间，请您耐心等待.</p>
<p>jQuery 加载完成前，您暂时无法操作本页面.</p>
<br />
<p>如果您长时间停留在此页面，请手动刷新网页.</p>
</div>
<div id="content-user" class="hidden">
<h2>用户管理</h2>
<table>
<thead><tr><td style="width: 40px">UID</td><td>用户名</td><td class="mobile_hidden">邮箱</td><td>操作</td></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="content-stat" class="hidden">
<h2>用户签到统计</h2>
<table>
<thead><tr><td style="width: 40px">UID</td><td>用户名</td><td>已成功</td><td>已跳过</td><td>待签到</td><td>待重试</td><td>不支持</td></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="content-setting" class="hidden">
<h2>系统设置</h2>
<p>云平台管理:</p>
<?php if ($sid = cloud::id()) { ?>
<p>站点ID: <?php echo $sid; ?></p>
<p>当前域名：<?php echo $siteurl; ?></p>
<p>
<a href="admin.php?action=cloud_sync&formhash=<?php echo $formhash; ?>" class="btn red" onclick="return msg_win_action(this.href)">同步站点信息</a>
<?php
if(!getSetting('use_sae_api')){
	echo '<a href="admin.php?action=use_sae_api&formhash='.$formhash.'" class="btn submit" onclick="return msg_redirect_action(this.href)">切换到 SAE API</a>';
} else {
	echo '<a href="admin.php?action=use_default_api&formhash='.$formhash.'" class="btn submit" onclick="return msg_redirect_action(this.href)">切换到默认 API</a>';
}
?>
</p>
<?php } else { ?>
<p>没有在云平台注册，请尝试刷新本页面</p>
<?php } ?>
<br>
<form method="post" action="admin.php?action=save_setting" id="setting_form" onsubmit="return post_win(this.action, this.id)">
<p>功能增强:</p>
<input type="hidden" name="formhash" value="<?php echo $formhash; ?>">
<p><label><input type="checkbox" id="random_sign" name="random_sign" /> 使用随机签到模式</label></p>
<p><label><input type="checkbox" id="multi_thread" name="multi_thread" /> 多线程签到 (Alpha, Nightly version only)</label></p>
<p><label><input type="checkbox" id="account_switch" name="account_switch" /> 允许多用户切换</label></p>
<p><label><input type="checkbox" id="autoupdate" name="autoupdate" /> 每天自动更新用户喜欢的贴吧 (稍占服务器资源)</label></p>
<p>功能限制:</p>
<p>
<select name="max_tieba" id="max_tieba">
<option value="0" selected>不限制单用户的最大喜欢贴吧数量</option>
<option value="50">每个用户最多喜欢 50 个贴吧</option>
<option value="80">每个用户最多喜欢 80 个贴吧</option>
<option value="100">每个用户最多喜欢 100 个贴吧</option>
<option value="120">每个用户最多喜欢 120 个贴吧</option>
<option value="180">每个用户最多喜欢 180 个贴吧</option>
<option value="250">每个用户最多喜欢 250 个贴吧</option>
</select>
</p>
<p>防恶意注册:</p>
<p><label><input type="checkbox" id="block_register" name="block_register" /> 彻底关闭新用户注册功能</label></p>
<p><label><input type="checkbox" id="register_check" name="register_check" /> 启用内置的简单防恶意注册系统 (可能会导致无法注册)</label></p>
<p><label><input type="checkbox" id="register_limit" name="register_limit" /> 限制并发注册 (开启后可限制注册机注册频率)</label></p>
<p><input type="text" name="invite_code" id="invite_code" placeholder="邀请码 (留空为不需要)" /></p>
<p>jQuery 加载方式:</p>
<p><label><input type="radio" id="jquery_1" name="jquery_mode" value="1" /> 从 Google API 提供的 CDN 加载 (默认, 推荐)</label></p>
<p><label><input type="radio" id="jquery_2" name="jquery_mode" value="2" /> 从 Sina App Engine 提供的 CDN 加载</label></p>
<p><label><input type="radio" id="jquery_3" name="jquery_mode" value="3" /> 从 Baidu App Engine 提供的 CDN 加载 (不支持 SSL)</label></p>
<p><label><input type="radio" id="jquery_4" name="jquery_mode" value="4" /> 使用程序自带的 jQuery 类库 (推荐)</label></p>
<p>网站备案编号:</p>
<p><input type="text" id="beian_no" name="beian_no" placeholder="未备案的不需要填写" /></p>
<p>网站副标题: (将显示在标题中)</p>
<p><input type="text" id="extra_title" name="extra_title" placeholder="如：KK 后宫团专用版" /></p>
<p><input type="submit" value="保存设置" /></p>
</form>
<br>
<p>邮件发送方式:</p>
<form method="post" action="admin.php?action=mail_setting" id="mail_setting" onsubmit="return post_win(this.action, this.id)">
<input type="hidden" name="formhash" value="<?php echo $formhash; ?>">
<?php
foreach($classes as $id=>$obj){
	$desc = $obj->description ? ' - '.$obj->description : '';
	if(!$obj->isAvailable()) $desc = ' (当前服务器环境不支持)';
	echo '<p><label><input type="radio" name="mail_sender" value="'.$id.'"'.($obj->isAvailable() ? '' : ' disabled').($id == getSetting('mail_class') ? ' checked' : '').' /> '.$obj->name.$desc.'</label></p>';
}
?>
<p>
<input type="submit" value="保存设置" />
 &nbsp; <a href="javascript:;" class="btn" id="mail_advanced_config">高级设置</a>
 &nbsp; <a href="admin.php?action=mail_test&formhash=<?php echo $formhash; ?>" class="btn" onclick="return msg_win_action(this.href)">发送测试</a>
</p>
</form>
</div>
<div id="content-mail" class="hidden">
<h2>邮件群发</h2>
<p>此功能用于向本站已经注册的所有用户发送邮件公告</p>
<p>为避免用户反感，建议您不要经常发送邮件</p>
<br>
<form method="post" action="admin.php?action=send_mail" id="send_mail" onsubmit="return post_win(this.action, this.id)">
<input type="hidden" name="formhash" value="<?php echo $formhash; ?>">
<p>邮件标题：</p>
<p><input type="text" name="title" style="width: 80%" /></p>
<p>邮件内容：</p>
<p><textarea name="content" rows="10" style="width: 80%"></textarea></p>
<p><input type="submit" value="确认发送" /></p>
</form>
</div>
<div id="content-plugin" class="hidden">
<h2>插件管理</h2>
<p>安装相关插件能够增强 贴吧签到助手 的相关功能.（部分插件可能会影响系统运行效率）</p>
<p>插件的设计可以参考 Github 上的项目介绍.</p>
<p>将插件文件放到 /plugins/ 文件夹下即可在此处看到对应的插件程序.</p>
<p>如果你觉得某个插件有问题，你可以先尝试禁用它，禁用操作不会丢失数据.</p>
<p>插件下载: <a href="http://www.kookxiang.com/forum-addon-1.html" target="_blank">http://www.kookxiang.com/forum-addon-1.html</a></p>
<table>
<thead><tr><td style="width: 40px">#</td><td>插件标识符 (ID)</td><td>插件介绍</td><td>当前版本</td><td>操作</td></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="content-template" class="hidden">
<h2>模板管理</h2>
<p>这里显示了当前安装的所有模板，你可以选择一个作为 贴吧签到助手 的模板显示.</p>
<p>将模板文件放到 /template/ 文件夹下即可在此处看到对应的模板.</p>
<p>模板的设计教程与下载可以访问: <a href="http://www.kookxiang.com/forum-addon-1.html" target="_blank">http://www.kookxiang.com/forum-addon-1.html</a></p>
<ul class="template-list">
</ul>
</div>
<div id="content-cron" class="hidden">
<h2>计划任务</h2>
<p>你可以通过这个表格检查系统任务的运行状态。</p>
<p>如果所有任务均处于队列中状态，证明您很可能没有添加计划任务。</p>
<table>
<thead><tr><td style="width: 40px">#</td><td>类型</td><td>计划任务脚本名</td><td>下次执行</td><td>当前状态</td></tr></thead>
<tbody></tbody>
</table>
<p><a href="admin.php?action=clear_cron&formhash=<?php echo $formhash; ?>" class="btn red" onclick="return msg_callback_action(this.href, load_cron)">清理无效任务</a></p>
</div>
<div id="content-updater" class="hidden">
<style type="text/css">
#content-updater .result { padding: 10px 15px; margin-bottom: 0; background: #efefef; }
#content-updater .filelist ul { margin-top: -5px; padding: 0 15px 10px; background: #efefef; }
#content-updater .filelist ul li { list-style: disc; line-height: 25px; margin: 0 0 0 25px; }
</style>
<h2>检测升级</h2>
<p>此功能将联网更新您的贴吧签到助手. 升级过程采用差量升级的方式.</p>
<p>升级过程需要保证文件被更新的文件可读可写.</p>
<br>
<p>如果更新过程出现错误，您可以到 <a href="http://buildbot.ikk.me/#sign" target="_blank">http://buildbot.ikk.me/#sign</a> 下载最新完整包进行覆盖</p>
<br>
<?php
if(getSetting('channel') == 'dev'){
	echo '<p>当前分支：开发版 (<a id="switch_to_stable" href="javascript:;">切换到稳定版</a>)</p>';
} else {
	echo '<p>当前分支：稳定版 (<a id="switch_to_dev" href="javascript:;">切换到开发版</a>)</p>';
}
?>
<p>开发版拥有更快的更新速度，但同时也拥有一定的不稳定性.</p>
<br>
<p class="result">正在检查更新...</p>
<div class="filelist hidden">
<ul></ul>
<p><button class="btn red">开始更新</button></p>
</div>
</div>
</div>
</div>
<?php include template('widget/footer'); ?>
<?php
if(defined('CLOUD_NOT_INITED')) echo '<div class="hidden"><img src="api.php?action=register_cloud" /></div>';
?>
</body>
</html>