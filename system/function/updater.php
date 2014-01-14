<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
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
?>