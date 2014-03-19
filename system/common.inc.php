<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_KKFRAME', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.14.2.6');

define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ERROR | E_WARNING | E_PARSE : E_ERROR | E_PARSE);

require_once SYSTEM_ROOT.'./class/error.php';
set_exception_handler(array('error', 'exception_error'));

if(!file_exists(SYSTEM_ROOT.'./config.inc.php')){
	header('Location: ./install/');
	exit();
}

function class_loader($class_name){
	list($type, $plugin_id) = explode('_', $class_name, 2);
	if ($type == 'plugin') {
		$file_path = "plugins/{$plugin_id}/plugin.class.php";
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

$system = new core();
$system->init();
$formhash = substr(md5(substr(TIMESTAMP, 0, -7).$username.$uid.ENCRYPT_KEY.ROOT), 8, 8);
$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
$siteurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
