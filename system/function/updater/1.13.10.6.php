<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query('ALTER TABLE `member` CHANGE `username` `username` VARCHAR(24)');
saveSetting('version', '1.13.10.13');
showmessage('成功更新到 1.13.10.13！', './');
?>