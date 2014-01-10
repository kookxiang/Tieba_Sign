<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query("ALTER TABLE `member_setting` ADD `zhidao_sign` TINYINT(1) NOT NULL DEFAULT '0'");
DB::query("ALTER TABLE `member_setting` ADD `wenku_sign` TINYINT(1) NOT NULL DEFAULT '0'");

saveSetting('version', '1.13.9.6');
showmessage('成功更新到 1.13.9.6！', './', 1);
?>