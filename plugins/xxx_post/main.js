$("#menu_xxx_post-index").click(function (){
	if($(".nav-tabs >.active").index()==0) {load_post_set();load_post_adv_set();}
	else if($(".nav-tabs >.active").index()==1) load_post_log();
});
$('#x_p_frequency').change(function(){
	if($('#x_p_frequency').val()==4) $("#x_p_runtimes_hide").fadeOut("slow");
	else $("#x_p_runtimes_hide").fadeIn("slow");
});
$("#xxx_post_add_tid").click(function(){
	createWindow().setTitle("添加帖子").setContent('<p>你可以指定帖子进行回复</p><p>请输入帖子的地址:</p><p>例如:http://tieba.baidu.com/p/2692275116</p><form method="get" action="plugin.php?id=xxx_post&action=get-tid" id="xxx_post_tid_form" onsubmit="return post_win(this.action, this.id,x_reload)"><input type="text" id="xxx_post_tid" name="xxx_post_tid" style="width:90%"/></form>').addButton("确定", function(){ $('#xxx_post_tid_form').submit(); }).addCloseButton("取消").append();
	});
$("#x_p_add_tb").click(function(){
	createWindow().setTitle("添加帖吧").setContent('<p>你可以只指定贴吧，并从该贴吧首页随机选择帖子进行回复</p><p>请输入帖吧的名字（不要带“吧”字）:</p><p>例如:要添加chrome吧，请输入chrome</p><form method="get" action="plugin.php?id=xxx_post&action=add-tieba" id="xxx_post_add_tb_form" onsubmit="return post_win(this.action, this.id,x_reload)"><input type="text" id="xxx_post_add_tieba" name="xxx_post_add_tieba" list="autocomplete-tieba" style="width:90%"/></form>').addButton("确定", function(){ $('#xxx_post_add_tb_form').submit(); }).addCloseButton("取消").append();
	});
$("#xxx_post_add_content").click(function(){
	createWindow().setTitle("添加回帖内容").setContent('<p>请输入要回复的内容（最多1000字符）:</p><form method="get" action="plugin.php?id=xxx_post&action=set-content" id="xxx_post_content_form" onsubmit="return post_win(this.action, this.id,x_reload)"><textarea name="post_content" id="post_content" rows="5" style="width: 95%"></textarea></form>').addButton("确定", function(){ $('#xxx_post_content_form').submit(); }).addCloseButton("取消").append();
	});
$("#x_p_add_con").click(function(){
	createWindow().setTitle("批量添加内容").setContent('<p>请输入要回复的内容（每行算一条）:</p><form method="get" action="plugin.php?id=xxx_post&action=set-cont-plus" id="x_p_cont_form" onsubmit="return post_win(this.action, this.id,x_reload)"><textarea name="x_p_contant" id="x_p_contant" rows="8" style="width: 95%"></textarea></form>').addButton("确定", function(){ $('#x_p_cont_form').submit(); }).addCloseButton("取消").append();
	});
$("#x_p_del_con").click(function(){
	createWindow().setTitle("批量删除").setContent('你确定要删除全部回复内容吗？').addButton("确定", function(){msg_callback_action('plugin.php?id=xxx_post&action=del-all-cont',x_reload);}).addCloseButton("取消").append();
});
$("#x_p_del_tid").click(function(){
	createWindow().setTitle("批量删除").setContent('你确定要删除全部贴子吗？').addButton("确定", function(){msg_callback_action('plugin.php?id=xxx_post&action=del-all-tid',x_reload);}).addCloseButton("取消").append();
});
$(".x_tab_content>div").each(function(i){
	$(this).addClass("x_tab_content_"+i);
	if(i!=0) $(this).hide();
});
$(".nav-tabs >li>a").click(function(){
	if($(this).parent().hasClass("active")) return 0;
	else{
		$(".x_tab_content>.x_tab_content_"+$(this).parent().siblings().filter(".active").index()).hide();
		$(this).parent().siblings().filter(".active").removeClass("active");
		$(".x_tab_content>.x_tab_content_"+$(this).parent().index()).show();
		$(this).parent().addClass("active");
		if($(this).parent().index()==0)  {load_post_set();load_post_adv_set();}
		else if($(this).parent().index()==1) load_post_log();
	}
});
function x_reload(){
	load_post_set();load_post_adv_set();
}
function load_post_set(){
	showloading();
	$.getJSON("plugin.php?id=xxx_post&action=post-settings", function(result){
		show_post_set(result);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取设置').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function show_post_set(result){
	$('#xxx_post_show').html('');
	$('#xxx_post_contents').html('');
	if(result.count1){
		$.each(result.tiebas, function(i, field){
		$("#xxx_post_show").append("<tr><td>"+(i+1)+"</td><td><a href=\"http://tieba.baidu.com/f?kw="+field.unicode_name+"\" target=\"_blank\">"+field.name+"</a></td><td><a href=\"http://tieba.baidu.com/p/"+field.tid+"\" target=\"_blank\">"+field.post_name+"</a></td><td><a href=\"javascript:;\" onclick=\"return delsid('"+field.sid+"')\">删除</a></td></tr>");
	});}else{
		$('#xxx_post_show').html('<tr><td colspan="4">暂无记录</td></tr>');
	}
	if(result.count2){
		$.each(result.contents, function(i, field){
		$("#xxx_post_contents").append("<tr><td>"+(i+1)+"</td><td>"+field.content+"</td><td><a href=\"javascript:;\" onclick=\"return delcont('"+field.cid+"')\">删除</a></td></tr>");
	});}else{
		$('#xxx_post_contents').html('<tr><td colspan="3">暂无记录</td></tr>');
	}
}
function load_post_adv_set(){
	showloading();
	$.getJSON("plugin.php?id=xxx_post&action=post-adv-settings", function(result){
		$('#x_p_client_type').val(result.settings.client_type).removeAttr('disabled');
		$('#x_p_frequency').val(result.settings.frequency).removeAttr('disabled');
		$('#x_p_delay').val(result.settings.delay).removeAttr('disabled');
		$('#x_p_runtimes').val(result.settings.runtimes).removeAttr('disabled');
		if(result.settings.frequency==4) $("#x_p_runtimes_hide").hide();
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取设置').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function load_post_log(){
	showloading();
	$.getJSON("plugin.php?id=xxx_post&action=post-log", function(result){
		show_post_log(result);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取回帖报告').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function load_post_history(date){
	showloading();
	$.getJSON("plugin.php?id=xxx_post&action=post-history&date="+date, function(result){
		show_post_log(result);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取签到报告').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function show_post_log(result){
	if(!result || result.count == 0){
		$('#x_p_log_tab').html('<tr><td colspan="5">暂无记录</td></tr>');
		return;
	}
	$('#x_p_log_tab').html('');
	$('#x_p_post_log_tite').html(result.date+" 回帖记录");
	$.each(result.log, function(i, field){
		$("#x_p_log_tab").append("<tr><td>"+(i+1)+"</td><td><a href=\"http://tieba.baidu.com/f?kw="+field.unicode_name+"\" target=\"_blank\">"+field.name+"</a></td><td><a href=\"http://tieba.baidu.com/p/"+field.tid+"\" target=\"_blank\">"+field.post_name+"</a></td><td>"+field.status+"</td><td>"+field.retry+"</td></tr>");
	});
	var pager_text = '';
	if(result.before_date) pager_text += '<a class="btn" onclick="return load_post_history('+result.before_date+')">&laquo; 前一天</a>';
	pager_text += '<a class="btn" onclick="load_post_log()">今天</a>';
	if(result.after_date) pager_text += '<a class="btn" onclick="return load_post_history('+result.after_date+')">后一天 &raquo;</a>';
	$('#x_p_pager_text').html(pager_text);
}
function delsid(sid){
	createWindow().setTitle('删除帖子').setContent('确认要删除这个帖子的自动回复吗？').addButton('确定', function(){ msg_callback_action("plugin.php?id=xxx_post&action=delsid&sid="+sid,x_reload); }).addCloseButton('取消').append();
	return false;
}
function delcont(cid){
	createWindow().setTitle('删除帖子').setContent('确认要删除这个回复内容吗？').addButton('确定', function(){ msg_callback_action("plugin.php?id=xxx_post&action=delcont&cid="+cid,x_reload); }).addCloseButton('取消').append();
	return false;
}
