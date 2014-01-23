<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
class Updater{
	const UPDATE_SERVER = 'http://update.ikk.me/';
	const UPDATE_ID = 'tieba_sign';
	public static function init(){
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
	public static function check(){
		$data = fetch_url(self::UPDATE_SERVER.'filelist.php?d='.self::UPDATE_ID);
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
		CACHE::save('need_download', $err_file);
		DB::query('DELETE FROM download');
		return $list;
	}
	public static function loop(){
		$file_list = CACHE::get('need_download');
		list($path, $hash) = array_pop($file_list);
		if(!$path) return array('status' => 1);
		$ret = self::_download_file($path, $hash);
		if ($ret<0) return array('status' => $ret, 'file' => $path);
		CACHE::save('need_download', $file_list);
		$max = sizeof(CACHE::get('kk_updater'));
		$current = DB::result_first('SELECT COUNT(*) FROM download');
		return array('status' => 0, 'precent' => round($current / $max * 100), 'file' => $path);
	}
	public static function write_file(){
		$err_file = $files = array();
		$query = DB::query('SELECT * FROM download');
		while($file = DB::fetch($query)){
			$file['content'] = pack('H*', $file['content']);
			$files[] = $file;
		}
		if(!$files) return array('status' => -255);
		foreach($files as $file) {
			if(!self::_is_writable(ROOT.$file['path'])) $err_file[] = $file['path'];
		}
		if($err_file) array('status' => -1, 'files' => $err_file);
		foreach($files as $file) {
			self::_write(ROOT.$file['path'], $file['content']);
			if(md5_file(ROOT.$file['path']) != md5($file['content'])) return array('status' => -2, 'file' => $file['path']);
		}
		DB::query('DELETE FROM download');
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
		$content = fetch_url(self::UPDATE_SERVER.'get_file.php?d='.self::UPDATE_ID."&f={$path}");
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
		DB::insert('download', array('path' => $path, 'content' => bin2hex($content)));
		return 0;
	}
}
?>