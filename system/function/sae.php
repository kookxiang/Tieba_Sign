<?php
if(!defined('IN_KKFRAME')) exit();

$_config = array(
	'db' => array(
		'server' => SAE_MYSQL_HOST_M,
		'port' => SAE_MYSQL_PORT,
		'username' => SAE_MYSQL_USER,
		'password' => SAE_MYSQL_PASS,
		'name' => SAE_MYSQL_DB,
		'pconnect' => false,
	),
);

$real_siteurl = 'http://'.$_SERVER['HTTP_APPNAME'].'.sinaapp.com/';

?>