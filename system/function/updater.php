<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
function check_update(){
	if(defined('UPDATE_CHECKED')) return;
	$ver = $_COOKIE['ver'];
	if($ver == VERSION) return;
	$query = DB::query("SELECT v FROM setting WHERE k='version'", 'SILENT');
	$res = DB::fetch($query);
	$current_version = $res['v'];
	dsetcookie('ver', $current_version);
	if ($current_version != VERSION){
		// load update script
		while($current_version){
			$filepath = SYSTEM_ROOT."./function/updater/{$current_version}.php";
			if(file_exists($filepath)){
				include $filepath;
				exit();
			} else{
				$current_version = substr($current_version, 0, strrpos($current_version, '.'));
			}
		}
		include SYSTEM_ROOT.'./function/updater/fallback.php';
		exit();
	} else{
		define('UPDATE_CHECKED', true);
		return;
	}
}
?>