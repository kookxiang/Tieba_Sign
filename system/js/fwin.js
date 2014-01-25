function createWindow(){
	var win = new Object();
	win.obj = document.createElement('div');
	win.obj.className = 'fwin';
	win.title = '提示信息';
	win.content = 'null';
	win.btns = document.createElement('p');
	win.btns.className = 'btns';
	win.allow_close = true;
	win.setTitle = function(str){
		this.title = str;
		return this;
	}
	win.setContent = function(str){
		this.content = str;
		return this;
	}
	win.addButton = function(title, callback){
		var btn = document.createElement('button');
		btn.className = "btn submit";
		btn.innerHTML = title;
		btn.onclick = function(){
			callback();
			win.close();
		}
		this.btns.appendChild(btn);
		return this;
	}
	win.addCloseButton = function(title){
		var btn = document.createElement('button');
		btn.className = "btn";
		btn.innerHTML = title;
		btn.onclick = function(){
			win.close();
		}
		this.btns.appendChild(btn);
		return this;
	}
	win.append = function(){
		if (this.allow_close) {
			var closebtn = document.createElement('span');
			closebtn.className = 'close';
			closebtn.innerText = 'x';
			closebtn.onclick = function(){
				win.close();
			};
			this.obj.appendChild(closebtn);
		}
		var win_title = document.createElement('h3');
		win_title.innerHTML = this.title;
		var obj = this.obj;
		win_title.onmousedown = function(event){ try{ dragMenu(obj, event, 1); }catch(e){} };
		win_title.unselectable = true;
		this.obj.appendChild(win_title);
		var win_content = document.createElement('div');
		win_content.className = 'fcontent';
		win_content.innerHTML = this.content;
		if (this.btns.innerHTML) {
			win_content.appendChild(this.btns);
		}
		this.obj.appendChild(win_content);
		$('#append_parent').append(this.obj);
		var top = ($('body').height() - this.obj.clientHeight) / 2;
		var left = ($('body').width() - this.obj.clientWidth) / 2;
		this.obj.style.top = top + 'px';
		this.obj.style.left = left + 'px';
		return false;
	}
	win.close = function(){
		win.obj.className = 'fwin h';
		setTimeout(function(){ $(win.obj).remove(); }, 300);
	}
	return win;
}
function msg_win_action(link){
	link += link.indexOf('?') < 0 ? '?' : '&';
	link += "format=json";
	showloading();
	$.getJSON(link, function(result){
		createWindow().setTitle('系统消息').setContent(result.msg).addCloseButton('确定').append();
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法解析返回结果').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
	return false;
}
function msg_redirect_action(link){
	link += link.indexOf('?') < 0 ? '?' : '&';
	link += "format=json";
	showloading();
	$.getJSON(link, function(result){
		createWindow().setTitle('系统消息').setContent(result.msg).addButton('确定', function(){ location.href = result.redirect; }).append();
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法解析返回结果').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
	return false;
}
function msg_callback_action(link, callback){
	link += link.indexOf('?') < 0 ? '?' : '&';
	link += "format=json";
	showloading();
	$.getJSON(link, function(result){
		createWindow().setTitle('系统消息').setContent(result.msg).addButton('确定', function(){ callback(); }).append();
	}).fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法解析返回结果').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
	return false;
}
function showloading(){
	$('.loading-icon').removeClass('hidden');
	$('.loading-icon').removeClass('h');
}
var loading_win_timer;
function hideloading(){
	$('.loading-icon').addClass('h');
	if(loading_win_timer) clearTimeout(loading_win_timer);
	loading_win_timer = setTimeout(function(){ $('.loading-icon').addClass('hidden'); }, 300);
}
function post_win(link, formid, callback, skip_win){
	link += link.indexOf('?') < 0 ? '?' : '&';
	link += "format=json";
	showloading();
	$.post(link, $('#'+formid).serialize(), function(result){
		if(!callback && result.redirect) callback = function(){ location.href = result.redirect; }
		if(skip_win) return callback();
		var win = createWindow().setTitle('系统消息').setContent(result.msg);
		if(callback){
			win.addButton('确定', callback);
		}else{
			win.addCloseButton('确定');
		}
		win.append();
	}, 'json').fail(function() { createWindow().setTitle('系统错误').setContent('发生未知错误: 无法解析返回结果').addButton('确定', function(){ location.reload(); }).append(); }).always(function(){ hideloading(); });
	return false;
}
var JSMENU = [];
function dragMenu(menuObj, e, op) {
	e = e ? e : window.event;
	if(op == 1) {
		JSMENU['drag'] = [e.clientX, e.clientY];
		JSMENU['drag'][2] = parseInt(menuObj.style.left);
		JSMENU['drag'][3] = parseInt(menuObj.style.top);
		document.onmousemove = function(e) { try{dragMenu(menuObj, e, 2); }catch(err){} };
		document.onmouseup = function(e) { try{dragMenu(menuObj, e, 3); }catch(err){} };
	}else if(op == 2 && JSMENU['drag'][0]) {
		var menudragnow = [e.clientX, e.clientY];
		menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
		menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
		menuObj.removeAttribute('top_');
		menuObj.removeAttribute('left_');
	}else if(op == 3) {
		JSMENU['drag'] = [];
		document.onmousemove = null;
		document.onmouseup = null;
	}
	return false;
}
