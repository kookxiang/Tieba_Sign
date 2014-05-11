<?php
if(!defined('IN_KKFRAME')) exit();

cron_set_nextrun($tomorrow);

CACHE::clear();
$date = date('Ymd', TIMESTAMP+900);
DB::query("INSERT IGNORE INTO sign_log (tid, uid, `date`) SELECT tid, uid, '{$date}' FROM my_tieba");
$delete_date = date('Ymd', TIMESTAMP - 86400*30);
DB::query("DELETE FROM sign_log WHERE date<'{$delete_date}'");
saveSetting('extsign_uid', 0);
saveSetting('autoupdate_uid', 0);
Updater::check();
cloud::check_remote_disabled();
