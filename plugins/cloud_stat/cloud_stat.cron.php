<?php
if(!defined('IN_KKFRAME')) exit();
$obj = $_PLUGIN['obj']['cloud_stat'];
if(!$obj && file_exists(ROOT.'plugins/cloud_stat/plugin.class.php')) {
	$obj = new plugin_cloud_stat();
}

$date = date('Ymd', TIMESTAMP);
$tieba = intval($obj->getSetting('tieba'));
$exp = intval($obj->getSetting('exp'));
$tieba += DB::result_first("SELECT COUNT(*) FROM sign_log WHERE status=2 AND date='{$date}'");
$obj->saveSetting('tieba', $tieba);
$exp += DB::result_first("SELECT SUM(exp) FROM sign_log WHERE status=2 AND date='{$date}'");
$obj->saveSetting('exp', $exp);

/* send data */

$sid = cloud::id();
$key = cloud::key();
$sign = md5($key.$sid.$tieba.$exp.$key);
$ret = kk_fetch_url("http://api.ikk.me/stat.php?sid={$sid}&tieba={$tieba}&exp={$exp}&sign={$sign}");
if($ret) {
	$data = json_decode($ret);
	if($data){
		$obj->saveSetting('cloud_tieba', $data->tieba);
		$obj->saveSetting('cloud_exp', $data->exp);
	}
}

cron_set_nextrun($tomorrow + 3600);
