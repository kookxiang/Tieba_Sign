<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query("DELETE FROM cron WHERE id='sign_retry'");
saveSetting('version', '1.14.5.12');
showmessage('成功更新到 1.14.5.12！', './');
?>