<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS `process` (
  `id` varchar(16) NOT NULL,
  `exptime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
EOF;
$res = DB::query($sql, 'SILENT');
if(!$res) DB::query(str_replace('MEMORY', 'MyISAM', $sql));
saveSetting('version', '1.14.5.20');
showmessage('成功更新到 1.14.5.20！', './');
?>