<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
define('UPDATE_SERVER', 'http://update.ikk.me/');
define('UPDATE_ID', 'tieba_sign');

function check_update(){
	global $_config;
	$current_version = $_config['version'];
	if ($current_version == VERSION) return;
	$version = $current_version;
	while($version){
		$filepath = SYSTEM_ROOT."./function/updater/{$version}.php";
		if(file_exists($filepath)){
			include $filepath;
			exit();
		} else{
			$version = substr($version, 0, strrpos($version, '.'));
		}
	}
	include SYSTEM_ROOT.'./function/updater/fallback.php';
	exit();
}

function save_config_file(){
	global $_config;
	if (!$_config) return;
	$content = '<?php'."\r\n/* Auto-generated config file */\r\n\$_config = ";
	$content .= var_export($_config, true).";\r\n?>";
	if(!is_writable(SYSTEM_ROOT.'./config.inc.php')) throw new Exception('Config file is not writable!');
	file_put_contents(SYSTEM_ROOT.'./config.inc.php', $content);
}

function saveVersion($version){
	global $_config;
	if (!$_config) return;
	$_config['version'] = $version;
	save_config_file();
}

function checkUpdate(){
	$data = fetch_url(UPDATE_SERVER.'filelist.php?d='.UPDATE_ID);
	if (!$data) return -1;
	$content = pack('H*', $data);
	$file_list = unserialize($content);
	unset($content);
	if (!$file_list) return -2;
	$err_file = $list = array();
	foreach($file_list as $file) {
		list($path, $hash) = explode("\t", $file);
		$file_hash = md5_file(ROOT."./{$path}");
		if ($file_hash != $hash){
			$err_file[] = array($path, $hash);
			$list[] = $path;
		}
	}
	if(!$list) return 0;
	sort($list);
	sort($err_file);
	CACHE::save('kk_updater', $err_file);
	return $list;
}

function upgrade_file(){
	$file_list = CACHE::get('kk_updater');
	if(!$file_list) return array('status' => -255);
	$err_file = array();
	foreach($file_list as $file) {
		list($path, $hash) = $file;
		if(!file_exists(ROOT."./{$path}")){
			if(!file_exists(dirname(ROOT."./{$path}"))) @mkdir(dirname(ROOT."./{$path}"), 0777, true);
			@touch(ROOT."./{$path}");
			@chmod(ROOT."./{$path}", 0777);
		}else{
			if(!is_writable(ROOT."./{$path}")) @chmod(ROOT."./{$path}", 0777);
		}
		if(!is_writable(ROOT."./{$path}")) $err_file[] = $path;
	}
	if($err_file) array('status' => -1, 'files' => $err_file);
	foreach($file_list as $file) {
		list($path, $hash) = $file;
		$ret = _download_file($path, $hash);
		if ($ret<0) return array('status' => -2, '_status' => $ret, 'file' => $path);
	}
	return array('status' => 0);
}

function _download_file($path, $hash, $try = 1) {
	$content = fetch_url(UPDATE_SERVER.'get_file.php?d='.UPDATE_ID."&f={$path}");
	if (!$content) {
		if ($try == 3) {
			return -1;
		} else {
			return download_file($path, $hash, $try + 1);
		}
	}
	if (md5($content) != $hash) {
		if ($try == 3) {
			return -2;
		} else {
			return download_file($path, $hash, $try + 1);
		}
	}
	@file_put_contents(ROOT."./{$path}", $content);
	$md5 = md5_file(ROOT."./{$path}");
	if ($md5 != $hash) return -3;
	return 0;
}

?>