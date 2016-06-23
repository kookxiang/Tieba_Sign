<?php
if(!defined('IN_KKFRAME')) exit();
$date = date('Ymd', TIMESTAMP);
$count = DB::result_first("SELECT * FROM `sign_log` WHERE status IN (0, 1) AND date='{$date}' LIMIT 0,1");
if($nowtime - $today < 1800){
	cron_set_nextrun($today + 1800);
}elseif(!$count){
	cron_set_nextrun($tomorrow + 1800);
}
