<?php
error_reporting(E_ALL ^ E_NOTICE);
define('IN_KKFRAME', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
define('TIMESTAMP', time());
define('VERSION', '1.13.12.25');

if($_GET['debug']){
	define('DEBUG_ENABLED', true);
	error_reporting(E_ALL ^ E_NOTICE);
}else{
	define('DEBUG_ENABLED', false);
	error_reporting(0);
}
require_once SYSTEM_ROOT.'./class/core.php';
$system = new kk_sign();
$formhash = substr(md5(substr(TIMESTAMP, 0, -7).$username.$uid.ENCRYPT_KEY.ROOT), 8, 8);
$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
$siteurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$sitepath.'/');
