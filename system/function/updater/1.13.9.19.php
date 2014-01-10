<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
DB::query('DROP TABLE IF EXISTS `cache`');
DB::query('CREATE TABLE IF NOT EXISTS `cache` (
  `k` varchar(32) NOT NULL,
  `v` TEXT NOT NULL,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');
saveSetting('version', '1.13.9.23');
showmessage('成功更新到 1.13.9.23！', './', 1);
?>