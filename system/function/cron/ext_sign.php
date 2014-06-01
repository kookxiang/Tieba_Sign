<?php
if(!defined('IN_KKFRAME')) exit();
$num = 0;
$_uid = getSetting('extsign_uid') ? getSetting('extsign_uid') : 1;
while($_uid){
	if(++$num > 20) exit();
	$setting = get_setting($_uid);
	if($setting['zhidao_sign']) zhidao_sign($_uid);
	if($setting['wenku_sign']) wenku_sign($_uid);
	$_uid = DB::result_first("SELECT uid FROM member WHERE uid>'{$_uid}' ORDER BY uid ASC LIMIT 0,1");
	saveSetting('extsign_uid', $_uid);
}
cron_set_nextrun($tomorrow + 1800);
