<?php
if(!defined('IN_KKFRAME')) exit();
$_config = array();

// ------------------ 系统设定 ------------------
$_config['adminid'] = '1';			// 超级管理员 UID，如有多个使用英文逗号分隔
// -------------- END 系统设定 ------------------

if(getenv('HTTP_BAE_ENV_ADDR_SQL_IP')){

// ------------------ BAE 数据库设定 ------------------
	$_config['db']['server'] = getenv('HTTP_BAE_ENV_ADDR_SQL_IP');
	$_config['db']['port'] = getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
	$_config['db']['username'] = getenv('HTTP_BAE_ENV_AK');
	$_config['db']['password'] = getenv('HTTP_BAE_ENV_SK');
	$_config['db']['name'] = 'GXQLIvEDHyaq';	// 修改成你的数据库名
// -------------- END BAE 数据库设定 ------------------

// ------------------ SAE 数据库设定 ------------------
}elseif(defined('SAE_MYSQL_DB')){						// 已自动设置好，无需干预
	$_config['db']['server'] = SAE_MYSQL_HOST_M;
	$_config['db']['port'] = SAE_MYSQL_PORT;
	$_config['db']['username'] = SAE_MYSQL_USER;
	$_config['db']['password'] = SAE_MYSQL_PASS;
	$_config['db']['name'] = SAE_MYSQL_DB;
}elseif($_ENV['JAE_MYSQL_USERNAME']){
// -------------- END SAE 数据库设定 ------------------

// ------------------ JAE 数据库设定 ------------------
	$_config['db']['server'] = $_ENV['JAE_MYSQL_IP'];		// JAE在后台绑定数据库之后就可以自动获取了
	$_config['db']['port'] = $_ENV['JAE_MYSQL_PORT'];
	$_config['db']['username'] = $_ENV['JAE_MYSQL_USERNAME'];
	$_config['db']['password'] = $_ENV['JAE_MYSQL_PASSWORD'];
	$_config['db']['name'] = $_ENV['JAE_MYSQL_DBNAME'];
}else{
// -------------- END JAE 数据库设定 ------------------

// ------------------ 非BAE、SAE 数据库设定 ------------------
	$_config['db']['server'] = 'localhost';			// 数据库服务器地址
	$_config['db']['port'] = '3306';				// 数据库端口
	$_config['db']['username'] = 'root';			// 数据库用户名
	$_config['db']['password'] = 'root';			// 数据库密码
	$_config['db']['name'] = 'kk_sign';				// 数据库名
}
// -------------- END 非BAE、SAE 数据库设定 ------------------

// 是否使用 MySQL 持续连接
$_config['db']['pconnect'] = false;

?>