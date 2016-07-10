<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

DB::query('ALTER TABLE `member` MODIFY `password` VARCHAR(255)');

saveSetting('version', '1.16.7.10');
showmessage('成功更新到 1.16.7.10！', './');
