<?php
if(!defined('IN_KKFRAME')) exit();

if(getSetting('autoupdate')){
	$num = 0;
	$_uid = getSetting('autoupdate_uid') ? getSetting('autoupdate_uid') : 1;
	while($__uid = $_uid){
		$_uid = DB::result_first("SELECT uid FROM member WHERE uid>'{$_uid}' ORDER BY uid ASC LIMIT 0,1");
		saveSetting('autoupdate_uid', $_uid);
		update_liked_tieba($__uid, true, false);
		if(++$num > 20)	exit();
	}
	saveSetting('autoupdate_uid', 0);
}
cron_set_nextrun($tomorrow);
