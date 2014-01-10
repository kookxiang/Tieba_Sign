<?php
if(!defined('IN_KKFRAME')) exit();

$query = DB::query('SELECT * FROM setting');
while($result = DB::fetch($query)){
	if(strexists($result['v'], '_mail_')) continue;
	$cache[ $result['k'] ] = $result['v'];
}
