<?php
define('IN_ADMINCP', true);
define('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';
$plugin_id = htmlspecialchars($_GET['id']);
$plugins = CACHE::get('plugins');
foreach($plugins as $plugin){
	if($plugin['id'] == $plugin_id) {
		$exists = true;
		break;
	}
}
if(!isset($exists)) throw new Exception("Unknown plugin '{$plugin_id}'");
$obj = HOOK::getPlugin($plugin_id);
if($obj instanceof Plugin){
	$obj->handleAction();
} else {
	throw new Exception('This plugin doesn\'t support to be called directly.');
}
