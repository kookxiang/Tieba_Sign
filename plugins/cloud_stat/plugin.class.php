<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_cloud_stat extends Plugin{
	var $description = '云统计，记录建站以来的签到次数以及获得经验数';
	var $modules = array(
		array('type' => 'page', 'id' => 'index', 'title' => '签到云统计', 'file' => 'index.inc.php'),
		array('type' => 'cron', 'cron' => array('id' => 'cloud_stat/cloud_stat', 'order' => '105')),
	);
	var $version = '1.1';
	function checkCompatibility(){
		if(version_compare(VERSION, '1.14.4.24', '<')) showmessage('本插件不兼容此版的贴吧签到助手.');
	}
	function install(){
		$count = DB::result_first('SELECT COUNT(*) FROM sign_log WHERE status=2');
		$this->saveSetting('tieba', $count);
		$count = DB::result_first('SELECT SUM(exp) FROM sign_log WHERE status=2');
		$this->saveSetting('exp', $count);
		$ret = kk_fetch_url("http://api.ikk.me/stat.php");
		if(!$ret) return;
		$data = json_decode($ret);
		if(!$data) return;
		$this->saveSetting('cloud_tieba', $data->tieba);
		$this->saveSetting('cloud_exp', $data->exp);
	}
	function mklink($sourceFile, $targetFile){
		return @file_put_contents($targetFile, '<?php @include '.var_export($sourceFile, true).'; ?>');
	}
	function on_upgrade($from_version){
		switch($from_version){
			case '1.0':
				DB::query("UPDATE cron SET id='cloud_stat/cloud_stat' WHERE id='cloud_stat'");
				return '1.1';
			default:
				throw new Exception("Unknown plugin version: {$from_version}");
		}
	}
	function handleAction(){
		echo json_encode(array(
			'ctieba' => intval($this->getSetting('cloud_tieba')),
			'cexp' => intval($this->getSetting('cloud_exp')),
			'tieba' => intval($this->getSetting('tieba')),
			'exp' => intval($this->getSetting('exp')),
		));
	}
}