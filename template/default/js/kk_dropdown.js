// KK Drop-down menu
(function(){
	function open_menu(obj){
		var menuid = 'dropdown_' + obj.getAttribute('menu-id');
		if(!document.getElementById(menuid)) return false;
		obj.hover = true;
		document.getElementById(menuid).onmouseover = function(){ obj.hover = true; };
		document.getElementById(menuid).onmouseout = function(){ obj.hover = false; };
		document.getElementById(menuid).style.display = 'block';
		document.getElementById(menuid).style.top = obj.offsetTop + obj.offsetHeight + 'px';
		var left = obj.offsetLeft + obj.offsetWidth - document.getElementById(menuid).offsetWidth;
		if(left <= 0){
			document.getElementById(menuid).style.left = obj.offsetLeft - 5 + 'px';
		}else{
			document.getElementById(menuid).style.left = left + 'px';
		}
		close_menu(obj);
	}
	function close_menu(obj, force){
		var menuid = 'dropdown_' + obj.getAttribute('menu-id');
		if(!document.getElementById(menuid)) return false;
		if(!force && (document.getElementById(menuid).hover || obj.hover)){
			setTimeout(function(){ close_menu(obj); }, 100);
		}else{
			document.getElementById(menuid).style.display = 'none';
		}
	}
	var selects = document.getElementsByTagName('select');
	var select;
	for(i in selects){
		select_obj = selects[i];
		if(typeof select_obj != 'object') continue;
		if(select_obj.inited) continue;
		select_obj.inited = true;
		select_obj.style.display = 'none';
		var dropdown_obj = document.createElement('ul');
		dropdown_obj.id = 'dropdown_' + i;
		dropdown_obj.className = 'dropdown';
		dropdown_obj.obj = select_obj;
		var select_replacer = document.createElement('div');
		dropdown_obj.replacer = select_replacer;
		select_obj.replacer = select_replacer;
		select_replacer.className = 'select';
		select_replacer.setAttribute("menu-id", i);
		select_replacer.obj = select_obj;
		var value_obj = document.createElement('span');
		select_replacer.value = value_obj;
		select_replacer.appendChild(value_obj);
		var icon_down = document.createElement('span');
		icon_down.className = "icon";
		icon_down.innerText = '▼';
		select_replacer.appendChild(icon_down);
		for(var o=0; o<select_obj.options.length; o++){
			var option_item = document.createElement('li');
			option_item.innerHTML = select_obj.options[o].innerHTML;
			var option_value = select_obj.options[o].value;
			option_item.setAttribute("value", option_value);
			option_item.onclick = function(){ this.parentNode.obj.value = this.getAttribute('value'); this.parentNode.replacer.value.innerHTML=this.innerHTML; close_menu(this.parentNode.replacer, true); };
			dropdown_obj.appendChild(option_item);
			if(select_obj.value == select_obj.options[o].value) select_replacer.value.innerHTML = select_obj.options[o].innerHTML;
			select_obj.onchange = function(){ for(var p=0; p<this.options.length; p++) { if(this.value == this.options[p].value) this.replacer.value.innerHTML = this.options[p].innerHTML; } return true; };
		}
		select_replacer.onmouseout = function(){ this.hover = false; };
		select_replacer.onmouseover = function(){ this.hover = true;}
		select_replacer.onclick = function(){ open_menu(this); };
		select_obj.parentNode.insertBefore(select_replacer, select_obj);
		select_obj.parentNode.insertBefore(dropdown_obj, select_obj);
	}
})();

// jQuery hooks
$.valHooks.select = {
	set: function(elem, value) {
		elem.value = value;
		elem.onchange();
	},
};
