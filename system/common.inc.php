<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_KKFRAME', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.14.1.15');

define('DEBUG_ENABLED', isset($_GET['debug']));
error_reporting(DEBUG_ENABLED ? E_ERROR | E_WARNING | E_PARSE : 0);

require_once SYSTEM_ROOT.'./class/core.php';

if(!file_exists(SYSTEM_ROOT.'./config.inc.php')){
	header('Location: ./install/');
	exit();
}

$system = new kk_sign();
$formhash = substr(md5(substr(TIMESTAMP, 0, -7).$username.$uid.ENCRYPT_KEY.ROOT), 8, 8);
$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
$siteurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
