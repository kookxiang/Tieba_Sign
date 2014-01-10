<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query("ALTER TABLE `my_tieba` ADD `skiped` TINYINT(1) NOT NULL DEFAULT '0'");

saveSetting('version', '1.13.9.5');
showmessage('成功更新到 1.13.9.5！', './', 1);
?>