<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

if($current_version == '1.13.9.5'){
	DB::query("ALTER TABLE `member_setting` ADD `zhidao_sign` TINYINT(1) NOT NULL DEFAULT '0'");
	DB::query("ALTER TABLE `member_setting` ADD `wenku_sign` TINYINT(1) NOT NULL DEFAULT '0'");
	saveSetting('version', '1.13.9.6');
	showmessage('成功更新到 1.13.9.6！', './', 1);
}elseif($current_version == '1.13.9.6'){
	saveSetting('version', '1.13.9.8');
	showmessage('成功更新到 1.13.9.8！', './', 1);
}elseif($current_version == '1.13.9.8'){
	DB::query('CREATE TABLE IF NOT EXISTS `cache` ( `k` varchar(32) NOT NULL, `v` TEXT NOT NULL, PRIMARY KEY (`k`)) ENGINE=InnoDB DEFAULT CHARSET=utf8');
	saveSetting('version', '1.13.9.23');
	showmessage('成功更新到 1.13.9.23！', './', 1);
}elseif($current_version == '1.13.9.23'){
	saveSetting('version', '1.13.9.24');
	showmessage('成功更新到 1.13.9.24！', './', 1);
}elseif($current_version == '1.13.9.24'){
	$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `cron` (
  `id` varchar(16) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `nextrun` int(10) unsigned NOT NULL,
  `order` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO `cron` (`id`, `enabled`, `nextrun`, `order`) VALUES
('daily', 1, 0, 0),
('update_tieba', 1, 0, 10),
('sign', 1, 0, 20),
('ext_sign', 1, 0, 50),
('mail', 1, 0, 100);
CREATE TABLE IF NOT EXISTS `mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
EOF;
	runquery($sql);
	saveSetting('version', '1.13.10.4');
	showmessage('成功更新到 1.13.10.4！<br><br>请修改计划任务为以下内容：<br>http://域名/cron.php &nbsp; * * * * *（每分钟一次）');
}elseif($current_version == '1.13.9.4'){
	DB::insert('cron', array(
		'id' => 'sign_retry',
		'enabled' => 1,
		'nextrun' => TIMESTAMP,
		'order' => '110',
	));
	saveSetting('version', '1.13.10.6');
	showmessage('成功更新到 1.13.10.6！', './');
}elseif($current_version == '1.13.10.6'){
	DB::query('ALTER TABLE `member` CHANGE `username` `username` VARCHAR(24)');
	saveSetting('version', '1.13.10.13');
	showmessage('成功更新到 1.13.10.13！', './', 1);
}elseif($current_version == '1.13.10.13'){
	DB::query('ALTER TABLE `my_tieba` DROP INDEX `name`');
	DB::query('ALTER TABLE `my_tieba` ADD INDEX (`uid`)');
	DB::query('ALTER TABLE `member_setting` DROP `use_bdbowser`, DROP `sign_method`');
	saveSetting('version', '1.13.10.20');
	showmessage('成功更新到 1.13.10.20！', './');
}elseif($current_version == '1.13.10.20'){
	saveSetting('version', '1.13.11.5');
	showmessage('成功更新到 1.13.11.5！', './');
}elseif($current_version == '1.13.11.5'){
	DB::query('
CREATE TABLE IF NOT EXISTS `plugin` (
  id int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  module text NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
');
	DB::insert('plugin', array('name' => 'debug_info'));
	DB::insert('plugin', array('name' => 'update_log'));
	saveSetting('version', '1.13.11.9');
	showmessage('成功更新到 1.13.11.9！', './');
}elseif($current_version == '1.13.11.9'){
	runquery("
ALTER TABLE `plugin` ADD `enable` TINYINT(1) NOT NULL DEFAULT '1' AFTER `id`;
ALTER TABLE `plugin` ADD `version` VARCHAR(8) NOT NULL DEFAULT '0';
ALTER TABLE `member_setting` ADD `cookie` TEXT BINARY CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
");
	$query = DB::query('SELECT uid, cookie FROM member');
	while($result = DB::fetch($query)){
		save_cookie($result['uid'], $result['cookie']);
	}
	DB::query('ALTER TABLE `member` DROP `cookie`');
	$query = DB::query('SHOW columns FROM `plugin`');
	while($result = DB::fetch($query)){
		if($result['Field'] == 'module') DB::query('ALTER TABLE `plugin` DROP `module`');
	}
	CACHE::clear();
	CACHE::update('plugins');
	saveSetting('register_limit', 1);
	saveSetting('register_check', 1);
	saveSetting('jquery_mode', 2);
	saveSetting('version', '1.13.12.15');
	showmessage('成功更新到 1.13.12.15！', './');
}elseif($current_version == '1.13.12.15'){
	saveSetting('version', '1.13.12.25');
	showmessage('成功更新到 1.13.12.25！', './');
}elseif($current_version == '1.13.12.25'){
	if($_config['adminid']) saveSetting('admin_uid', $_config['adminid']);
	saveSetting('version', '1.14.1.15');
	showmessage('成功更新到 1.14.1.15！', './');
}

?>