<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_update_log{
	var $description = '前台“新功能推荐”菜单，告诉用户有什么新鲜的功能';
	var $modules = array(
		array('id' => 'log', 'type' => 'page', 'title' => '新功能推荐', 'file' => 'log.inc.php'),
	);
}