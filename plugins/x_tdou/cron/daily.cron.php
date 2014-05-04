<?php
if (!defined('IN_KKFRAME')) exit();
$date = date('Ymd', TIMESTAMP + 900);
DB::query("alter table x_tdou_log CHANGE `date` `date` INT NOT NULL DEFAULT '{$date}'");
DB::query("insert ignore into x_tdou_log (uid) SELECT uid FROM member");
$delete_date = date('Ymd', TIMESTAMP - 86400 * 30);
DB::query("DELETE FROM x_tdou_log WHERE date<'$delete_date'");
define('CRON_FINISHED', true);
