<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
if($_config['adminid']) saveSetting('admin_uid', $_config['adminid']);
saveSetting('version', '1.14.1.15');
showmessage('成功更新到 1.14.1.15！', './');
?>