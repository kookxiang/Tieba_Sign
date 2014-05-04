<?php
if(!getenv('OPENSHIFT_APP_NAME')){
	header('Location: ./');
	exit();
}
error_reporting(E_ERROR | E_PARSE);
$config_file = dirname(__FILE__).'/../system/config.inc.php';
include_once $config_file;
if($_config){
	header('Location: ..');
	exit();
}

@touch($config_file);

switch($_GET['step']){
	default:
		$content = '<p>欢迎使用 贴吧签到助手 安装向导！</p><p>本程序将会指引你在服务器上配置好“贴吧签到助手”</p><p>点击右侧的“下一步”按钮开始</p><br><p>Openshift one-key installer. Thanks to <a href="http://tieba.baidu.com/home/main?un=%D3%F4%C3%C6de%CB%B5" target="_blank">郁闷de说</a></p><p class="btns"><button onclick="location.href=\'./openshift.php?step=database\';">下一步 &raquo;</button>';
		show_install_page('Welcome', $content);
		break;
	case 'database':
		$content = '<div class="config"><p>请设置站点管理员信息</p><br>';
		$content .= '<form action="./openshift.php?step=install" method="post" onsubmit="show_waiting();">';
		$content .= '<p><span>管理员用户名:</span><input type="text" name="username" required /></p>';
		$content .= '<p><span>管理员密码:</span><input type="password" name="password" required /></p>';
		$content .= '<p><span>管理员邮箱:</span><input type="text" name="email" required /></p>';
		$content .= '<p class="btns"><span>&nbsp;</span><input type="submit" value="下一步 &raquo;" /></p>';
		$content .= '</form></div><div class="waiting hidden"><p>程序正在执行必要的安装步骤，请耐心等待...</p></div>';
		$content .= '<script type="text/javascript">function show_waiting(){ $(".config").hide(); $(".waiting").show(); }</script>';
		show_install_page('站点配置', $content);
		break;
	case 'install':
		$db_host = getenv('OPENSHIFT_MYSQL_DB_HOST');
		$db_port = intval(getenv('OPENSHIFT_MYSQL_DB_PORT'));
		$db_username = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
		$db_password = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
		$db_name = getenv('OPENSHIFT_APP_NAME');
		$db_pconnect = false;
		$function = $db_pconnect ? 'mysql_connect' : 'mysql_pconnect';
		$link = mysql_connect("{$db_host}:{$db_port}", $db_username, $db_password);
		if(!$link) show_back('数据库配置', '错误：无法连接数据库服务器！</p><p>'.mysql_error());
		$selected = mysql_select_db($db_name, $link);
		if(!$selected){
			// 尝试新建
			mysql_query("CREATE DATABASE `{$db_name}`", $link);
			$selected = mysql_select_db($db_name, $link);
			if(!$selected) show_back('数据库配置', '错误：指定的数据库不可用</p><p>'.mysql_error());
		}
		mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
		$syskey = random(32);
		$username = addslashes($_POST['username']);
		$password = md5($syskey.md5($_POST['password']).$syskey);
		$email = addslashes($_POST['email']);
		if(!$username || !$password || !$email) show_back('注册账号', '您输入的信息不完整');
		if(preg_match('/[<>\'\\"]/i', $username)) show_back('注册账号', '用户名中有被禁止使用的关键字');
		if(strlen($username) < 6) show_back('注册账号', '用户名至少要6个字符(即2个中文 或 6个英文)，请修改');
		if(strlen($username) > 24) show_back('注册账号', '用户名过长，请修改');
		$install_script = file_get_contents(dirname(__FILE__).'/install.sql');
		preg_match('/version ([0-9a-z.]+)/i', $install_script, $match);
		$version = trim($match[1]);
		if(!$version) show_back('正在安装', '安装脚本有误，请重新上传');
		$err = runquery($install_script, $link);
		mysql_query("INSERT INTO member SET username='{$username}', password='{$password}', email='{$email}'");
		$uid = mysql_insert_id($link);
		mysql_query("INSERT INTO member_setting SET uid='{$uid}', cookie=''");
		saveSetting('block_register', 1);
		saveSetting('jquery_mode', 2);
		saveSetting('admin_uid', $uid);
		saveSetting('SYS_KEY', $syskey);
		if($err) show_back('正在安装', '安装过程出现错误:</p><p>'.mysql_error());
		$_config = array(
			'version' => $version,
			'db' => array(
				'server' => $db_host,
				'port' => $db_port,
				'username' => $db_username,
				'password' => $db_password,
				'name' => $db_name,
				'pconnect' => $db_pconnect,
				),
			);
		$content = '<?php'.PHP_EOL.'/* Auto-generated config file */'.PHP_EOL.'$_config = ';
		$content .= var_export($_config, true).';'.PHP_EOL.'?>';
		file_put_contents($config_file, $content);
		$content = '<p>贴吧签到助手 已经成功安装！</p><p>系统默认关闭用户注册，如果有需要，请到后台启用用户注册功能。</p><p style="color: red">Openshift 用户如出现错误请前往管理界面重启应用</p><br><p class="btns"><button onclick="location.href=\'../\';">登录 &raquo;</button>';
		show_install_page('安装成功', $content);
}

function show_back($title, $text){
	$content = '<p>'.$text.'</p>';
	$content .= '<br><p class="btns"><button onclick="history.back();">&laquo; 返回</button></p>';
	show_install_page($title, $content);
}

function show_install_page($title, $content){
	$template = '<!DOCTYPE html><html><head><title>贴吧签到助手</title><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><meta name="HandheldFriendly" content="true" /><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" /><meta name="author" content="kookxiang" /><meta name="copyright" content="KK\'s Laboratory" /><link rel="shortcut icon" href="../favicon.ico" /><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><meta name="renderer" content="webkit"><link rel="stylesheet" href="../template/default/style/main.css" type="text/css" /><link rel="stylesheet" href="../template/default/style/custom.css" type="text/css" /><style type="text/css">.status.on, .status.off { padding: 2px 0 2px 20px; } .status.on { color: #22dd33; background: url(../template/default/style/done.gif) no-repeat 1px 50%; } .status.off { color: #ff3344; background: url(../template/default/style/error.gif) no-repeat 1px 50%; } .main-box { max-width: 550px; top: 135px; } .main-content { min-height: 150px; text-align: left; font-size: 13px; padding-bottom: 18px; margin-left: 0; } .main-content>div { min-height: 0; } .main-wrapper { min-height: 150px; } .config span { width: 100px; text-align:right; display:block; float: left; height: 30px; line-height: 30px; margin-right: 15px; } .wrapper:after { display:block; content: \'.\'; padding-bottom: 175px; }</style></head><body><div class="wrapper" id="page_index"><div id="append_parent"><div class="loading-icon"><img src="../template/default/style/loading.gif" /> 载入中...</div></div><div class="main-box clearfix"><h1>贴吧签到助手 - 安装向导</h1><div class="main-wrapper"><div class="main-content"><h2>{title}</h2>{content}</div></div></div></div><script src="//lib.sinaapp.com/js/jquery/1.10.2/jquery-1.10.2.min.js"></script><script src="../template/default/js/fwin.js"></script><script type="text/javascript">hideloading();</script></body></html>';
	echo str_replace(array('{title}', '{content}'), array($title, $content), $template);
	exit();
}

function show_status($status, $on_txt = 'On', $off_txt = 'Off'){
	return $status ? '<span class="status on">'.$on_txt.'</span>' : '<span class="status off">'.$off_txt.'</span>';
}

function runquery($sql, $link){
	$sql = str_replace("\r", "\n", $sql);
	foreach(explode(";\n", trim($sql)) as $query) {
		$query = trim($query);
		if(!$query) continue;
		$ret = mysql_query($query, $link);
		if(!$ret) return mysql_error();
	}
}

function saveSetting($k, $v){
	global $link;
	$v = addslashes($v);
	mysql_query("REPLACE INTO setting SET v='{$v}', k='{$k}'", $link);
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

?>