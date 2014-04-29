<?php
if(!defined('IN_KKFRAME')) exit();
$date = date('Ymd', TIMESTAMP+900);
DB::query("ALTER TABLE xxx_post_log CHANGE `date` `date` INT NOT NULL DEFAULT '{$date}'");
DB::query("INSERT IGNORE INTO xxx_post_log (sid, uid) SELECT sid, uid FROM xxx_post_posts");
$delete_date = date('Ymd', TIMESTAMP - 86400*10);
DB::query("DELETE FROM xxx_post_log WHERE date<'$delete_date'");
$setime=HOOK::getPlugin('xxx_post')->getSetting('se');
if(!$setime) $setime=21;
$nxrun =$today+$setime*3600;
DB::query("update cron set nextrun='$nxrun' where id='xxx_post/c_se'");
cron_set_nextrun($tomorrow + 600);
