<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query('ALTER TABLE `member_setting` DROP `zhidao_sign`');
DB::query('ALTER TABLE `cron` CHANGE `id` `id` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
$next_day = mktime(0, 0, 0) + 86400;
$next_cron = $next_day + 1800;
DB::query("UPDATE cron SET nextrun='{$next_cron}' WHERE enabled='0'");
DB::query("UPDATE cron SET nextrun='{$next_day}' WHERE enabled='0' AND id='daily'");
saveSetting('version', '1.14.4.24');
showmessage('成功更新到 1.14.4.24！', './');
?>