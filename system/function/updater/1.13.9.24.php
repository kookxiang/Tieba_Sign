<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
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

$sql_array = explode(';', $sql);
foreach ($sql_array as $sql){
	$sql = trim($sql);
	if($sql) DB::query($sql);
}
saveSetting('version', '1.13.10.4');
showmessage('成功更新到 1.13.10.4！<br><br>请修改计划任务为以下内容：<br>http://域名/cron.php &nbsp; * * * * *（每分钟一次）');
?>