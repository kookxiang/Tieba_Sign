<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
$query = DB::query('SHOW TABLES');
$tables = array();
while($table = DB::fetch($query)){
	$tables[] = implode('', $table);
}

if(!in_array('member', $tables)){
	include SYSTEM_ROOT.'./function/updater/install.php';
}
throw new Exception("找不到更新程序，无法进行更新！<br>Error while upgrade from version {$current_version} to version ".VERSION.'.');
?>