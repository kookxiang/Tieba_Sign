<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_cloud_stat extends Plugin{
	var $description = '云统计，记录建站以来的签到次数以及获得经验数';
	var $modules = array(
		array('type' => 'page', 'id' => 'index', 'title' => '签到云统计', 'file' => 'index.inc.php'),
		array('type' => 'cron', 'cron' => array('id' => 'cloud_stat', 'order' => '105')),
	);
	var $version = '1.0';
	function install(){
		$count = DB::result_first('SELECT COUNT(*) FROM sign_log WHERE status=2');
		$this->saveSetting('tieba', $count);
		$count = DB::result_first('SELECT SUM(exp) FROM sign_log WHERE status=2');
		$this->saveSetting('exp', $count);
		$this->mklink(ROOT.'plugins/cloud_stat/cloud_stat.cron.php', ROOT.'system/function/cron/cloud_stat.php');
		if(!file_exists(ROOT.'system/function/cron/cloud_stat.php')){
			DB::query("UPDATE `plugin` SET `enable`=1 WHERE name='cloud_stat'");
			CACHE::update('plugins');
			showmessage('创建文件 system/function/cron/cloud_stat.php 失败，请检查文件权限', 'admin.php#plugin#');
		}
		$ret = fetch_url("http://api.ikk.me/stat.php");
		if(!$ret) return;
		$data = json_decode($ret);
		if(!$data) return;
		$this->saveSetting('cloud_tieba', $data->tieba);
		$this->saveSetting('cloud_exp', $data->exp);
	}
	function mklink($sourceFile, $targetFile){
		return @file_put_contents($targetFile, '<?php @include '.var_export($sourceFile, true).'; ?>');
	}
}