<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
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
?>