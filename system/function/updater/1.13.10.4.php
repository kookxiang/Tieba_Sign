<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::insert('cron', array(
	'id' => 'sign_retry',
	'enabled' => 1,
	'nextrun' => TIMESTAMP,
	'order' => '110',
));
saveSetting('version', '1.13.10.6');
showmessage('成功更新到 1.13.10.6！', './');
?>