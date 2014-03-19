<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_debug_info extends Plugin{
	var $description = '此插件将会在页脚输出一行调试信息，便于了解服务器状态';
	var $modules = array();
	function page_footer(){
		return DEBUG::output();
	}
}