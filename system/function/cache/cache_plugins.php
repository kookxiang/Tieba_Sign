<?php
if(!defined('IN_KKFRAME')) exit();

$query = DB::query("SELECT * FROM `plugin` WHERE `enable`='1'");
while($result = DB::fetch($query)){
	$cache[ $result['id'] ] = array('id' => $result['name'], 'ver' => $result['version']);
}
