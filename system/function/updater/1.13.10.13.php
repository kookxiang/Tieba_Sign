<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query('ALTER TABLE `my_tieba` DROP INDEX `name`');
DB::query('ALTER TABLE `my_tieba` ADD INDEX (`uid`)');
DB::query('ALTER TABLE `member_setting` DROP `use_bdbowser`, DROP `sign_method`');
saveSetting('version', '1.13.10.20');
showmessage('成功更新到 1.13.10.20！', './');
?>