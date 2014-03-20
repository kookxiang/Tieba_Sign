<?php
if(!defined('IN_KKFRAME')) exit();

CACHE::clear();
$date = date('Ymd', TIMESTAMP+900);
DB::query("ALTER TABLE sign_log CHANGE `date` `date` INT NOT NULL DEFAULT '{$date}'");
DB::query("INSERT IGNORE INTO sign_log (tid, uid) SELECT tid, uid FROM my_tieba");
$delete_date = date('Ymd', TIMESTAMP - 86400*30);
DB::query("DELETE FROM sign_log WHERE date<'{$delete_date}'");
saveSetting('extsign_uid', 0);
saveSetting('autoupdate_uid', 0);
DB::query("UPDATE cron SET enabled='0' WHERE id='daily'");
Updater::check();
cloud::check_remote_disabled();
