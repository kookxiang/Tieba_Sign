$(document).ready(function() {
	$('#menu>li').click(function (){
		if(isMobile()) $('.sidebar').fadeOut();
		if($(this).attr('id') == 'menu_updater') return;
		if($(this).attr('id') == 'menu_admincp') return;
		if($(this).hasClass('selected')) return;
		$('.menu li.selected').removeClass('selected');
		$(this).addClass('selected');
		var content_id = $(this).attr('id').replace('menu_', '#content-');
		$('.main-content>div').addClass('hidden');
		$(content_id).removeClass('hidden');
		var callback = $(this).attr('id').replace('menu_', 'load_').replace('-', '_');
		eval('if (typeof '+callback+' == "function") '+callback+'(); ');
	});
	$('#show_cookie_setting').click(function (){
		$('.tab-cookie').toggleClass('hidden');
	});
	$('#unbind_btn').click(function(){
		var link = this.href;
		createWindow().setTitle('解除绑定').setContent('确认要解除绑定吗？<br>(解除绑定后自动签到将停止，所有记录将被清除)').addButton('确定', function(){ msg_callback_action(link, load_baidu_bind); }).addCloseButton('取消').append();
		return false;
	});
	$('.menu_switch_user a').click(function(){
		var link = this.href;
		createWindow().setTitle('切换账号').setContent('确认要切换登陆账号吗？').addButton('确定', function(){ msg_redirect_action(link); }).addCloseButton('取消').append();
		return false;
	});
	$('.menu_switch_user .del').click(function(){
		var link = this.getAttribute('href');
		createWindow().setTitle('解除绑定').setContent('确认要解除账号绑定吗？').addButton('确定', function(){ msg_redirect_action(link); }).addCloseButton('取消').append();
		return false;
	});
	$('#menu_adduser a').click(function(){
		createWindow().setTitle('绑定账号').setContent('<form method="post" action="member.php?action=bind_user" id="bind_form" onsubmit="return post_win(this.action, this.id)"><input type="hidden" name="formhash" value="'+formhash+'"><p>使用此功能，你可以快速切换在本站注册的多个帐号。</p><p>输入您的用户名/密码即可绑定到本账号。</p><p><label>用户名： <input type="text" name="username" style="width: 200px" /></label></p><p><label>密　码： <input type="password" name="password" style="width: 200px" /></label></p></form>').addButton('确定', function(){ $('#bind_form').submit(); }).addCloseButton('取消').append();
		return false;
	});
	$('#menu_password').click(function(){
		createWindow().setTitle('修改密码').setContent('<form method="post" action="index.php?action=change_password" id="password_form" onsubmit="return post_win(this.action, this.id)"><input type="hidden" name="formhash" value="'+formhash+'"><p>经常修改密码是个好习惯哦 :)</p><p><label>原密码：　 <input type="password" name="old_password" style="width: 200px" /></label></p><p><label>新密码：　 <input type="password" name="new_password" style="width: 200px" /></label></p><p><label>再次输入： <input type="password" name="new_password2" style="width: 200px" /></label></p></form>').addButton('确定', function(){ $('#password_form').submit(); }).addCloseButton('取消').append();
		return false;
	});
	$('#menu_logout').click(function(){
		createWindow().setTitle('退出').setContent('确认要退出登录吗？').addButton('确定', function(){ location.href='member.php?action=logout&hash='+formhash; }).addCloseButton('取消').append();
		return false;
	});
	$('.menubtn').click(function(){
		$('.sidebar').fadeIn();
		autohide_sidebar();
	});
	$('.avatar').click(function(){
		$('#member-menu').fadeIn(100);
		autohide_membermenu();
	});
	$('#member-menu li a').click(function(){
		$('#member-menu').fadeOut(300);
	});
	$(window).on('hashchange', function() {
		parse_hash();
	});
	hideloading();
	while(location.hash.lastIndexOf('#') > 0) location.hash = location.hash.substring(0, location.hash.lastIndexOf('#'));
	parse_hash();
	// Load JS
	load_js();
	if(new_version) upgrade_tips();
	loadTiebaAutoComplete();
});

var guide_viewed = false;
var stat = [];
if (typeof defered_js == 'undefined') var defered_js = new Array;
stat[0] = stat[1] = stat[2] = stat[3] = stat[4] = 0;
var new_version = false;
function load_liked_tieba(){
	showloading();
	$.getJSON("ajax.php?v=liked_tieba", function(result){
		if(!result) return;
		$('#content-liked_tieba table tbody').html('');
		var tieba_name = new Array;
		var tieba_uname = new Array;
		$.each(result, function(i, field){
			if(typeof localStorage != 'undefined'){
				tieba_name.push(field.name);
				tieba_uname.push(field.unicode_name);
			}
			$("#content-liked_tieba table tbody").append("<tr><td>"+(i+1)+"</td><td><a href=\"http://tieba.baidu.com/f?kw="+field.unicode_name+"\" target=\"_blank\">"+field.name+"</a></td><td><input type=\"checkbox\" value=\""+field.tid+"\""+(field.skiped=='1' ? ' checked' : '')+" class=\"skip_sign\" /></td></tr>");
		});
		if(typeof localStorage != 'undefined'){
			localStorage['tieba_name'] = tieba_name.join('||');
			localStorage['tieba_uname'] = tieba_uname.join('||');
		}
		loadTiebaAutoComplete();
		$('#content-liked_tieba .skip_sign').click(function(){
			showloading();
			this.disabled = 'disabled';
			$.getJSON('index.php?action=skip_tieba&format=json&tid='+this.value+'&formhash='+formhash, function(result){ load_liked_tieba(); }).fail(function() { hideloading(); createWindow().setTitle('系统错误').setContent('发生未知错误: 无法修改当前贴吧设置').addCloseButton('确定').append(); });
			return false;
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取喜欢的贴吧列表').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function load_sign_log(){
	showloading();
	$.getJSON("ajax.php?v=sign-log", function(result){
		if(result.count == 0 && !guide_viewed){
			$('#menu_guide').click();
			return;
		}
		show_sign_log(result);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取签到报告').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function load_sign_history(date){
	$('.menu li.selected').removeClass('selected');
	$('.main-content>div').addClass('hidden');
	$('#content-sign_log').removeClass('hidden');
	showloading();
	$.getJSON("ajax.php?v=sign-history&date="+date, function(result){
		show_sign_log(result);
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取签到报告').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function show_sign_log(result){
	stat[0] = stat[1] = stat[2] = stat[3] = stat[4] = 0;
	if(!result || result.count == 0) return;
	$('#content-sign_log table tbody').html('');
	$('#content-sign_log h2').html(result.date+" 签到记录");
	$.each(result.log, function(i, field){
		$("#content-sign_log table tbody").append("<tr><td>"+(i+1)+"</td><td><a href=\"http://tieba.baidu.com/f?kw="+field.unicode_name+"\" target=\"_blank\">"+field.name+"</a></td><td>"+_status(field.status)+"</td><td>"+_exp(field.exp)+"</td></tr>");
	});
	var result_text = "";
	result_text += "共计 "+(stat[0] + stat[1] + stat[2] + stat[3] + stat[4])+" 个贴吧";
	result_text += ", 成功签到 "+(stat[4])+" 个贴吧";
	if(stat[2]) result_text += ", 有 "+(stat[2])+" 个贴吧尚未签到";
	if(stat[0]) result_text += ", 已跳过 "+(stat[0])+" 个贴吧";
	if(stat[3]) result_text += ", "+(stat[3])+" 个贴吧正在等待重试";
	if(stat[1]) result_text += ", "+(stat[1])+" 个贴吧无法签到, <a href=\"index.php?action=reset_failure&formhash="+formhash+"\" onclick=\"return msg_redirect_action(this.href)\">点此重置无法签到的贴吧</a>";
	$('#sign-stat').html(result_text);
	var pager_text = '';
	if(result.before_date) pager_text += '<a href="#history-'+result.before_date+'">&laquo; 前一天</a> &nbsp; ';
	if(!$('#menu_sign_log').hasClass('selected')) pager_text += '<a href="#signlog">今天</a>';
	if(result.after_date) pager_text += ' &nbsp; <a href="#history-'+result.after_date+'">后一天 &raquo;</a>';
	$('#page-flip').html(pager_text);
}
function load_setting(){
	showloading();
	$.getJSON("ajax.php?v=get-setting", function(result){
		if(!result) return;
		$('#error_mail').attr('checked', result.error_mail == "1");
		$('#send_mail').attr('checked', result.send_mail == "1");
		$('#wenku_sign').attr('checked', result.wenku_sign == "1");
		$('#bdbowser').removeAttr('disabled');
		$('#error_mail').removeAttr('disabled');
		$('#send_mail').removeAttr('disabled');
		$('#wenku_sign').removeAttr('disabled');
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取系统设置').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function load_guide(){
	guide_viewed = true;
}
function load_baidu_bind(){
	showloading();
	$.getJSON("ajax.php?v=get-bind-status", function(result){
		if(!result) return;
		$('#content-baidu_bind .tab').addClass('hidden');
		if(result.no == 0){
			$('#content-baidu_bind .tab-binded').removeClass('hidden');
			$('.tab-binded div').removeClass('hidden');
			$('.tab-binded div').html('');
			var avatar_img = 'http://tb.himg.baidu.com/sys/portrait/item/' + result.data.user_portrait;
			$('#avatar_img').attr('src', avatar_img);
			$('#avatar_img').removeClass('hidden');
			$('.tab-binded div').append('<img src="' + avatar_img + '" class="float-left">');
			$('.tab-binded div').append('<p>百度通行证：<a href="http://tieba.baidu.com/home/main?un=' + result.data.user_name_url + '" target="_blank">' + result.data.user_name_show + '</a></p>');
			$('.tab-binded div').append('<p>安全手机：' + result.data.mobilephone + '</p>');
			$('.tab-binded div').append('<p>安全邮箱：' + result.data.email + '</p>');
		}else{
			$('#content-baidu_bind .tab-bind').removeClass('hidden');
		}
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取绑定状态').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
}
function _status(status){
	if(typeof status == 'undefined') status = 0;
	status = parseInt(status);
	stat[ (status+2) ]++;
	if(isMobile()){
		switch(status){
			case -2:	return '<img src="template/default/style/warn.png" />';
			case -1:	return '<img src="template/default/style/error.gif" />';
			case 0:		return '<img src="template/default/style/retry.gif" />';
			case 1:		return '<img src="template/default/style/warn.png" />';
			case 2:		return '<img src="template/default/style/done.gif" />';
		}
	}else{
		switch(status){
			case -2:	return '跳过签到';
			case -1:	return '无法签到';
			case 0:		return '待签到';
			case 1:		return '签到失败';
			case 2:		return '已签到';
		}
	}
}
function _exp(exp){
	if(typeof exp == 'undefined') exp = 0;
	return parseInt(exp) == 0 ? '-' : '+'+exp;
}
function parse_hash(){
	var hash = location.hash.substring(1);
	if(hash.indexOf('#') >= 0){
		location.href = location.href.substring(0, location.href.lastIndexOf('#'));
		location.reload();
		return;
	}
	if(hash == "guide"){
		$('#menu_guide').click();
	}else if(hash == "liked_tieba"){
		guide_viewed = true;
		$('#menu_liked_tieba').click();
	}else if(hash == "sign_log"){
		$('#menu_sign_log').click();
	}else if(hash == "baidu_bind"){
		$('#menu_baidu_bind').click();
	}else if(hash == "setting"){
		$('#menu_setting').click();
	}else if(hash.split('-')[0] == "history"){
		load_sign_history(hash.split('-')[1]);
	}else if($('#menu_'+hash).length > 0){
		$('#menu_'+hash).click();
	}else{
		$('#menu_sign_log').click();
	}
}
function autohide_membermenu(){
	if($("#member-menu:hover").length > 0) return setTimeout(autohide_membermenu, 500);
	if($(".avatar:hover").length > 0) return setTimeout(autohide_membermenu, 500);
	$('#member-menu').fadeOut(300);
}
function autohide_sidebar(){
	if($(".sidebar:hover").length > 0) return setTimeout(autohide_sidebar, 500);
	if($(".menubtn:hover").length > 0) return setTimeout(autohide_sidebar, 500);
	$('.sidebar').fadeOut();
}
function isMobile(){
	return $('body').width() <= 550;
}
function load_js(){
	for(id in defered_js){
		var script;
		script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = defered_js[id] + '?' + Math.random();
		document.getElementsByTagName('head')[0].appendChild(script);
	}
}
function loadTiebaAutoComplete(){
	if(typeof localStorage == 'undefined') return;
	$('#autocomplete-tieba').remove();
	$('#append_parent').append('<datalist id="autocomplete-tieba" class="hidden"></datalist>');
	var tieba = localStorage['tieba_name'].split('||');
	if(tieba.length == 0) return;
	for(var i=0; i<tieba.length; i++){
		$('#autocomplete-tieba').append('<option value="'+tieba[i].replace('"', "&quot;")+'">');
	}
}