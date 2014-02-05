<?php
if(!defined('IN_KKFRAME')) exit();

$cache = array();
$query = DB::query("SELECT * FROM `plugin_var`");
while($result = DB::fetch($query)){
	if(!$cache[ $result['pluginid'] ]) $cache[ $result['pluginid'] ] = array();
	$cache[ $result['pluginid'] ][ $result['key'] ] = $result['value'];
}
