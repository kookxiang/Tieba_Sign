<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_KKFRAME', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.14.6.2');
define('UI_VERSION', '1.0');

define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ALL & !E_NOTICE & !E_STRICT : E_ERROR | E_PARSE);
@ini_set('display_errors', DEBUG_ENABLED);

require_once SYSTEM_ROOT.'./class/error.php';
set_exception_handler(array('error', 'exception_error'));

function class_loader($class_name){
	list($type, $plugin_id) = explode('_', strtolower($class_name), 2);
	if ($type == 'plugin' && $plugin_id) {
		$file_path = "plugins/{$plugin_id}/plugin.class.php";
	} elseif ($type == 'widget') {
		$file_path = "system/class/widget/{$class_name}.php";
	} elseif ($type == 'mail' || $class_name == 'mailer') {
		$file_path = "system/class/mail.php";
	} else {
		$file_path = "system/class/{$class_name}.php";
	}
	$real_path = ROOT.strtolower($file_path);
	if (!file_exists($real_path)) {
		throw new Exception('Ooops, system file is losing: '.strtolower($file_path));
	} else {
		require_once $real_path;
	}
}

if (function_exists('spl_autoload_register')){
	spl_autoload_register('class_loader');
}else{
	function __autoload($class_name){
		class_loader($class_name);
	}
}

require_once SYSTEM_ROOT.'./function/core.php';

// support for xae
if(defined('SAE_ACCESSKEY')){
	define('IN_SAE', true);
	define('IN_XAE', true);
	require_once SYSTEM_ROOT.'./function/sae.php';
}

if(!defined('IN_XAE') && !file_exists(SYSTEM_ROOT.'./config.inc.php')){
	header('Location: ./install/');
	exit();
}

$system = new core();
$system->init();
$formhash = substr(md5(substr(TIMESTAMP, 0, -7).$username.$uid.ENCRYPT_KEY.ROOT), 8, 8);
$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
$siteurl = htmlspecialchars(($_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$sitepath.'/');
