<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
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
?>