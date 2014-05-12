<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
?>
<h2>豆票获取记录</h2><p style="color:#757575;font-size:12px">当前插件版本：<?php echo HOOK::getPlugin('x_tdou')->version; ?> | 更新日期：<?php echo HOOK::getPlugin('x_tdou')->update_time; ?> | Designed By <a href="http://tieba.baidu.com/home/main?un=%D0%C7%CF%D2%D1%A9&fr=index" target="_blank">@星弦雪</a> && <a href="http://www.ikk.me" target="_blank">@kookxiang</a></p>
<p>这个插件可以帮助你自动领取豆票在线奖励以及自动砸蛋</p>
<table>
	<thead><tr><td style="width:20px">日期</td><td>豆票数量</td></tr></thead>
	<tbody id="x_tdou_log">
		<tr><td colspan="2"><img src="./template/default/style/loading.gif">载入中，请稍候...</td></tr>
	</tbody>
</table>
<a href="plugin.php?id=x_tdou&action=test" class="btn" onclick="return msg_win_action(this.href)">测试</a>
<script type="text/javascript">
function load_x_tdou_log(){
	showloading();
	$.getJSON("plugin.php?id=x_tdou&action=show_log", function(result){
		$('#x_tdou_log').html('');
		if(result.count){
			$.each(result.logs, function(i, field){
			$("#x_tdou_log").append("<tr><td>"+field.date+"</td><td>"+field.num+"</td></tr>");
		});}else{
			$('#x_tdou_log').html('<tr><td colspan="2">暂无记录</td></tr>');
		}
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取设置').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
</script>