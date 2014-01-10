<?php
if(!defined('IN_KKFRAME')) exit();

$query = DB::query("SELECT uid, username FROM member");
while($result = DB::fetch($query)){
	$cache[ $result['uid'] ] = $result['username'];
}
