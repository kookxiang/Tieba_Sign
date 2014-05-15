$(document).ready(function() {
	$('#menu>li').click(function (){
		if(isMobile()) $('.sidebar').fadeOut();
		if(!$(this).attr('id')) return;
		if($(this).hasClass('selected')) return;
		$('.menu li.selected').removeClass('selected');
		$(this).addClass('selected');
		var content_id = $(this).attr('id').replace('menu_', '#content-');
		$('.main-content>div').addClass('hidden');
		$(content_id).removeClass('hidden');
		var callback = $(this).attr('id').replace('menu_', 'load_');
		eval('if (typeof '+callback+' == "function") '+callback+'(); ');
	});
	$('#content-updater .filelist button').click(function(){
		$('#content-updater .filelist').hide();
		$('#content-updater .result').html('正在更新系统文件，请耐心等待...');
		updater_get_file();
	});
	$('#switch_to_dev').click(function(){
		switch_channel('dev', '<p>确定要切换到开发版么？</p><p>开发版具有一定的不稳定性，且有可能无法切换回稳定版</p>');
	});
	$('#switch_to_stable').click(function(){
		switch_channel('stable', '<p>确定要切换到稳定版么？</p><p>如果开发版版本号与稳定版不同，可能导致系统无法使用。<br>切换前请慎重考虑！</p>');
	});
	$('#mail_advanced_config').click(function(){
		post_win($('#mail_setting').attr('action'), 'mail_setting', function(){
			showloading();
			$.getJSON("admin.php?action=mail_advanced", function(result){
				if(!result) return;
				var content = '';
				for(var i=0; i<result.length; i++){
					content += '<p>'+result[i].name+':'+(result[i].description ? ' ('+result[i].description+')' : '')+'</p><p>';
					content += '<input type="'+result[i].type+'" name="'+result[i].key+'" value="'+result[i].value+'" style="width: 95%" />';
					content += '</p>';
				}
				if(result.length == 0){
					content += '<p>此邮件接口无高级设置项目</p>';
				}
				createWindow().setTitle('邮件高级设置').setContent('<form method="post" action="admin.php?action=mail_advanced" id="advanced_mail_config" onsubmit="return post_win(this.action, this.id)"><input type="hidden" name="formhash" value="'+formhash+'">'+content+'</form>').addButton('确定', function(){ $('#advanced_mail_config').submit(); }).addCloseButton('取消').append();
			}).fail(function() { createWindow().setTitle('邮件高级设置').setContent('发生未知错误: 无法打开高级设置面板').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
		}, true);
		return false;
	});
	$('.menubtn').click(function(){
		$('.sidebar').fadeIn();
		autohide_sidebar();
	});
	$(window).on('hashchange', function() {
		parse_hash();
	});
	hideloading();
	while(location.hash.lastIndexOf('#') > 0) location.hash = location.hash.substring(0, location.hash.lastIndexOf('#'));
	parse_hash();
});

function load_user(){
	showloading();
	$.getJSON("admin.php?action=load_user", function(result){
		if(!result) return;
		$('#content-user table tbody').html('');
		$.each(result, function(i, field){
			$("#content-user table tbody").append("<tr><td>"+field.uid+"</td><td>"+field.username+"</td><td class=\"mobile_hidden\">"+field.email+"</td><td><a href=\"admin.php?action=update_liked_tieba&uid="+field.uid+"&formhash="+formhash+"\" onclick=\"return msg_win_action(this.href)\">"+(isMobile() ? '刷新' : '刷新喜欢的贴吧')+"</a> | <a href=\"javascript:;\" onclick=\"return deluser('"+field.uid+"')\">"+(isMobile() ? '删除' : '删除用户')+"</a></td></tr>");
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取用户列表').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function switch_channel(channel, tips){
	createWindow().setTitle('切换分支').setContent(tips).addButton('确定', function(){ msg_redirect_action("admin.php?action=switch_channel&channel="+channel+"&formhash="+formhash); }).addCloseButton('取消').append();
}
function updater_get_file(){
	$.getJSON("admin.php?action=get_file", function(result){
		if(!result) return;
		if(result.status == 1){
			return updater_write_file();
		}else if(result.status < 0){
			$('#content-updater .result').html('更新过程出现错误！');
			switch(result.status){
				case -1:	$('#content-updater .result').append('下载文件 '+result.file+' 失败');	break;
				case -2:	$('#content-updater .result').append('校验文件 '+result.file+' 出错');	break;
				case -3:	$('#content-updater .result').append('当前环境不支持自动更新，请手动更新');	break;
			}
			setTimeout(function(){ location.reload(); }, 5000);
			return;
		}
		$('#content-updater .result').html('正在下载 '+result.file+'，请耐心等待... ('+result.precent+'%)');
		setTimeout(updater_get_file, 50);
	}).fail(function() { $('#content-updater .result').html('发生未知错误: 程序意外终止'); setTimeout(load_updater, 3000); });
}
function updater_write_file(){
	$.getJSON("admin.php?action=write_file", function(result){
		if(!result) return;
		if(result.status == -1){
			$('#content-updater .result').html('以下文件不可写入，请设置好权限后再进行升级');
			$('#content-updater .filelist').show();
			$('#content-updater .filelist ul').html('');
			$.each(result.files, function(i, field){
				$('#content-updater .filelist ul').append('<li>'+field+'</li>');
			});
			return;
		}else if(result.status == -2){
			$('#content-updater .result').html('更新过程出现错误！');
			switch(result._status){
				case -1:	$('#content-updater .result').append('下载文件 '+result.file+' 失败');	break;
				case -2:	$('#content-updater .result').append('校验文件 '+result.file+' 出错');	break;
			}
			setTimeout(function(){ location.reload(); }, 5000);
			return;
		}
		$('#content-updater .result').html('文件更新结束！');
		setTimeout(function(){ location.reload(); }, 1500);
	}).fail(function() { $('#content-updater .result').html('发生未知错误: 程序意外终止'); setTimeout(load_updater, 3000); });
}
function load_stat(){
	showloading();
	$.getJSON("admin.php?action=load_userstat", function(result){
		if(!result) return;
		$('#content-stat table tbody').html('');
		$.each(result, function(i, field){
			if(parseInt(field.unsupport) > 0) field.unsupport += ' (<a href="admin.php?action=reset_failure&uid='+field.uid+'&formhash='+formhash+'" onclick="return msg_win_action(this.href)">重置</a>)';
			$("#content-stat table tbody").append("<tr><td>"+field.uid+"</td><td>"+field.username+"</td><td>"+field.succeed+"</td><td>"+field.skiped+"</td><td>"+field.waiting+"</td><td>"+field.retry+"</td><td>"+field.unsupport+"</td></tr>");
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取用户统计数据').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function load_updater(){
	$('#content-updater .filelist').hide();
	$('#content-updater .result').html('正在检查更新...');
	$.getJSON("admin.php?action=update_check", function(result){
		if(!result) return;
		if(result.status<0){
			$('#content-updater .result').html('错误: 与更新服务器断开连接');
			return;
		}else if(result.status == 0){
			$('#content-updater .result').html('所有文件都是最新的！');
			return;
		}
		$('#content-updater .result').html('以下文件可以更新: ');
		$('#content-updater .filelist').show();
		$('#content-updater .filelist ul').html('');
		$.each(result.files, function(i, field){
			$('#content-updater .filelist ul').append('<li>'+field+'</li>');
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取数据').addCloseButton('确定').append(); });
}
function load_setting(){
	showloading();
	$.getJSON("admin.php?action=load_setting", function(result){
		if(!result) return;
		$('#account_switch').attr('checked', result.account_switch == 1);
		$('#register_check').attr('checked', result.register_check == 1);
		$('#register_limit').attr('checked', result.register_limit == 1);
		$('#autoupdate').attr('checked', result.autoupdate == 1);
		$('#block_register').attr('checked', result.block_register == 1);
		$('#random_sign').attr('checked', result.random_sign == 1);
		$('#multi_thread').attr('checked', result.multi_thread == 1);
		$('#invite_code').attr('value', result.invite_code ? result.invite_code : '');
		$('#beian_no').attr('value', result.beian_no ? result.beian_no : '');
		$('#stat_code').html(result.stat_code ? result.stat_code : '');
		$('#max_tieba').val(result.max_tieba);
		$('#extra_title').val(result.extra_title);
		$('input[name=jquery_mode]').attr('checked', false);
		switch(result.jquery_mode){
			case '1': case '2': case '3': case '4': $('#jquery_'+result.jquery_mode).prop('checked', true); break;
			default: $('#jquery_1').prop('checked', true);
		}
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取当前系统设置').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function load_cron(){
	showloading();
	$.getJSON("admin.php?action=load_cron", function(result){
		if(!result) return;
		$('#content-cron table tbody').html('');
		$.each(result, function(i, field){
			var content = '';
			content += '<tr>';
			content += '<td>'+(i+1)+'</td>';
			content += '<td>'+field.type+'</td>';
			content += '<td>'+field.id+'</td>';
			if(field.nextrun > 0){
				content += '<td>'+format_time(field.nextrun)+'后</td>';
				content += '<td>执行完毕</td>';
			}else{
				content += '<td>'+format_time(-field.nextrun)+'前</td>';
				content += '<td>队列中</td>';
			}
			content += '</tr>';
			$('#content-cron table tbody').append(content);
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取计划任务列表').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function format_time(time){
	if(time > 604800){
		return '>7天';
	}else if(time > 86400){
		return Math.floor(time / 86400)+'天';
	}else if(time > 3600){
		return Math.floor(time / 3600)+'小时';
	}else if(time > 60){
		return Math.floor(time / 60)+'分钟';
	}else{
		return time+'秒';
	}
}
function load_plugin(){
	showloading();
	$.getJSON("admin.php?action=load_plugin", function(result){
		if(!result) return;
		$('#content-plugin table tbody').html('');
		$.each(result, function(i, field){
			var mod_actions = '';
			if(field.installed){
				if(field.enabled){
					mod_actions += '<a href="admin.php?action=disable_plugin&pluginid='+field.id+'&formhash='+formhash+'" class="link_disable">禁用</a> | ';
					if(field.config) mod_actions += '<a href="admin.php?action=config_plugin&pluginid='+field.id+'" class="link_config">设置</a> | ';
				}else{
					mod_actions += '<a href="admin.php?action=enable_plugin&pluginid='+field.id+'&formhash='+formhash+'" class="link_enable">启用</a> | ';
				}
				mod_actions += '<a href="admin.php?action=uninstall_plugin&pluginid='+field.id+'&formhash='+formhash+'" class="link_uninstall">卸载</a>';
			}else{
				mod_actions += '<a href="admin.php?action=install_plugin&pluginid='+field.id+'&formhash='+formhash+'" class="link_install">安装</a>';
			}
			$("#content-plugin table tbody").append("<tr><td>"+(i+1)+"</td><td>"+field.id+"</td><td>"+field.description+"</td><td>"+field.version+"</td><td>"+mod_actions+"</td></tr>");
		});
		$('.link_enable, .link_disable').click(function(){
			return msg_callback_action(this.href, load_plugin);
		});
		$('.link_install').click(function(){
			var link = this.href;
			createWindow().setTitle('安装插件').setContent('<p>确定要安装这个插件吗？</p>').addButton('确定', function(){ msg_callback_action(link, load_plugin); }).addCloseButton('取消').append();
			return false;
		});
		$('.link_uninstall').click(function(){
			var link = this.href;
			createWindow().setTitle('卸载插件').setContent('<p>确定要卸载这个插件吗？</p><p>(卸载后该插件的数据可能会丢失)</p>').addButton('确定', function(){ msg_callback_action(link, load_plugin); }).addCloseButton('取消').append();
			return false;
		});
		$('.link_config').click(function(){
			var link = this.href;
			showloading();
			$.getJSON(link, function(result){
				createWindow().setTitle('插件设置').setContent('<form method="post" action="'+link+'" id="plugin_config" onsubmit="return post_win(this.action, this.id)"><input type="hidden" name="formhash" value="'+formhash+'">'+result.html+'</form>').addButton('确定', function(){ $('#plugin_config').submit(); }).addCloseButton('取消').append();
			}).fail(function() { createWindow().setTitle('插件设置').setContent('发生未知错误: 无法打开插件设置面板').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
			return false;
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取插件列表').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function load_template(){
	showloading();
	$.getJSON("admin.php?action=load_template", function(result){
		if(!result) return;
		$('#content-template .template-list').html('');
		$.each(result, function(i, field){
			var content = '<li'+(field.current==true?' class="current"':'')+' templateid="'+field.id+'"name="'+field.name+'" author="'+field.author+'" site="'+field.site+'" version="'+field.version+'"><div><img src="'+field.preview+'" title="预览图片"/><div><p>'+field.name+'</p></div>';
			if(field.current == true) $("#content-template .template-list").prepend(content);
			else $("#content-template .template-list").append(content);
		});
		$('#content-template .template-list li').click(function(){
			var obj = $(this);
			var current = obj.attr('class')=='current';
			var title = current ? obj.attr('name') + ' (当前模板)' : obj.attr('name') + ' ('+obj.attr('version')+')';
			var tips = '<p style="width:100%;"><img style="width:100%; border:1px solid #979595; border-radius:3px;margin:8px 0 -10px 0;" src="'+obj.find('img').eq(0).attr('src')+'" /></p><p>感谢本模板作者 <a href="'+obj.attr('site')+'" target="_blank">'+obj.attr('author')+'</a></p>';
			var tipsWindow = createWindow().setTitle(title).setContent(tips);
			if (current) tipsWindow.addCloseButton('关闭');
			else tipsWindow.addButton('使用此模板', function(){ msg_redirect_action("admin.php?action=set_template&template="+obj.attr('templateid')+"&formhash="+formhash); }).addCloseButton('关闭');
			tipsWindow.append();
		});
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法获取模板列表').addCloseButton('确定').append(); }).always(function(){ hideloading(); });
}
function parse_hash(){
	var hash = location.hash.substring(1);
	if(hash.indexOf('#') >= 0){
		location.href = location.href.substring(0, location.href.lastIndexOf('#'));
		location.reload();
		return;
	}
	if(hash == "user"){
		$('#menu_user').click();
	}else if(hash == "stat"){
		$('#menu_stat').click();
	}else if(hash == "setting"){
		$('#menu_setting').click();
	}else if(hash == "mail"){
		$('#menu_mail').click();
	}else if(hash == "plugin"){
		$('#menu_plugin').click();
	}else if(hash == "template"){
		$('#menu_template').click();
	}else if(hash == "cron"){
		$('#menu_cron').click();
	}else if(hash == "updater"){
		$('#menu_updater').click();
	}else{
		$('#menu_user').click();
	}
}
function deluser(uid){
	createWindow().setTitle('删除用户').setContent('确认要删除该用户吗？').addButton('确定', function(){ msg_win_action("admin.php?action=deluser&uid="+uid+"&formhash="+formhash); }).addCloseButton('取消').append();
	return false;
}
function autohide_sidebar(){
	if($(".sidebar:hover").length > 0) return setTimeout(autohide_sidebar, 500);
	if($(".menubtn:hover").length > 0) return setTimeout(autohide_sidebar, 500);
	$('.sidebar').fadeOut();
}
function isMobile(){
	return $('body').width() <= 550;
}
