<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query('ALTER TABLE `member_setting` ADD `zhidao_sign` TINYINT(1) NOT NULL AFTER `wenku_sign`');
saveSetting('version', '1.14.6.2');
showmessage('成功更新到 1.14.6.2！', './');
?>