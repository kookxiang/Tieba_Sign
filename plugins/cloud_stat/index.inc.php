<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
$obj = $_PLUGIN['obj']['cloud_stat'];
?>
<script type="text/javascript">
function load_cloud_stat_index(){
	showloading();
	$.getJSON("plugin.php?id=cloud_stat&action=get", function(result){
		if(!result) return;
		var str = '';
		str += '<p>截止今天, 贴吧签到助手共完成 <span>'+result.ctieba+'</span> 次签到</p>';
		str += '<p>为贴吧用户获取 <span>'+result.cexp+'</span> 点经验.</p>';
		str += '<p>其中, 当前网站签到 <span>'+result.tieba+'</span> 次, 获取了 <span>'+result.exp+'</span> 点经验.</p>';
		$('.kk_cloud_stat').html(str);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 获取统计信息失败').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
</script>
<h2>签到云统计 *</h2>
<style type="text/css">
.kk_cloud_stat { padding: 30px 20px; }
.kk_cloud_stat p { margin: 10px 0; font-size: 26px; line-height: 42px; font-weight: lighter; text-align: center; }
.kk_cloud_stat span { font-size: 32px; font-family: "Segoe UI Light", "Segoe UI", "幼圆", "Arial"; letter-spacing: 5px; vertical-align: baseline; font-size: 64px; text-shadow: 0 0 15px #777; position: relative; top: 5px; }
.stat_source { position: absolute; bottom: 10px; }
</style>
<div class="kk_cloud_stat">
</div>
<p class="stat_source">* 数据源自 贴吧签到助手 开放平台 旗下所有签到站点.</p>