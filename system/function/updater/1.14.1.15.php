<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query("ALTER TABLE `setting` CHANGE `v` `v` VARCHAR(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
saveSetting('version', '1.14.1.16');
showmessage('成功更新到 1.14.1.16！', './');
?>