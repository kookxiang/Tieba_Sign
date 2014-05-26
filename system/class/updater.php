<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
class Updater{
	const UPDATE_SERVER = 'http://update.ikk.me/';
	public static function init(){
		global $_config;
		if($_config['version']){
			$current_version = $_config['version'];
		} else {
			$current_version = getSetting('version');
		}
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
	public static function check(){
		$d = getSetting('channel') == 'dev' ? 'tieba_sign' : 'tieba_sign_stable';
		$p = implode(',', self::_getPluginList());
		$data = kk_fetch_url(self::UPDATE_SERVER."filelist.php?d={$d}&plugins={$p}");
		saveSetting('new_version', 0);
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
		saveSetting('new_version', 1);
		sort($list);
		sort($err_file);
		CACHE::save('kk_updater', $err_file);
		CACHE::save('need_download', $err_file);
		DB::query('DELETE FROM download');
		return $list;
	}
	public static function loop(){
		if(defined('IN_XAE')) return array('status' => -3);
		$file_list = CACHE::get('need_download');
		list($path, $hash) = array_pop($file_list);
		if(!$path) return array('status' => 1);
		$ret = self::_download_file($path, $hash);
		if ($ret<0) return array('status' => $ret, 'file' => $path);
		CACHE::save('need_download', $file_list);
		$max = sizeof(CACHE::get('kk_updater'));
		$current = $max - sizeof($file_list);
		return array('status' => 0, 'precent' => round($current / $max * 100), 'file' => $path);
	}
	public static function write_file(){
		$err_file = $files = array();
		$query = DB::query('SELECT * FROM download ORDER BY path ASC');
		while($file = DB::fetch($query)){
			list($part, $path) = explode('|', $file['path'], 2);
			if(!$files[ $path ]){
				$file['content'] = pack('H*', $file['content']);
				$files[ $path ] = $file;
			} else {
				$files[ $path ]['content'] .= pack('H*', $file['content']);
			}
		}
		if(!$files) return array('status' => -255);
		foreach($files as $path => $file) {
			if(!self::_is_writable(ROOT.$path)) $err_file[] = $path;
		}
		if($err_file) array('status' => -1, 'files' => $err_file);
		foreach($files as $path => $file) {
			self::_write(ROOT.$path, $file['content']);
			if(md5_file(ROOT.$path) != md5($file['content'])) return array('status' => -2, 'file' => $path);
		}
		DB::query('DELETE FROM download');
		saveSetting('new_version', 0);
		return array('status' => 0);
	}
	private static function _write($path, $content){
		$fp = @fopen($path, 'wb');
		if(!$fp) return false;
		fwrite($fp, $content);
		fclose($fp);
		return true;
	}
	private static function _is_writable($path){
		if(!file_exists($path)){
			if(!file_exists(dirname($path))) @mkdir(dirname($path), 0777, true);
			@touch($path);
			@chmod($path, 0777);
		}else{
			if(!is_writable($path)) @chmod($path, 0777);
		}
		return is_writable($path);
	}
	private static function _download_file($path, $hash, $try = 1) {
		$d = getSetting('channel') == 'dev' ? 'tieba_sign' : 'tieba_sign_stable';
		$content = kk_fetch_url(self::UPDATE_SERVER."get_file.php?d={$d}&f={$path}");
		if (!$content) {
			if ($try == 3) {
				return -1;
			} else {
				return self::_download_file($path, $hash, $try + 1);
			}
		}
		if (md5($content) != $hash) {
			if ($try == 3) {
				return -2;
			} else {
				return self::_download_file($path, $hash, $try + 1);
			}
		}
		$length = $part = 0;
		while($length < strlen($content)){
			$part++;
			$part_length = strlen($content) - $length > 8192 ? 8192 : strlen($content) - $length;
			$_countent = substr($content, $length, $part_length);
			$length += $part_length;
			$_part = str_pad($part, 4, "0", STR_PAD_LEFT);
			DB::insert('download', array('path' => "{$_part}|".$path, 'content' => bin2hex($_countent)));
		}
		return 0;
	}
	private static function _getPluginList(){
		$pluginList = array();
		$list_dir = dir(ROOT.'./plugins/');
		while($dirName = $list_dir->read()){
			if($dirName == '.' || $dirName == '..' || !is_dir(ROOT."./plugins/{$dirName}")) continue;
			$pluginList[] = $dirName;
		}
		return $pluginList;
	}
}
?>