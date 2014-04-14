<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');
saveSetting('template', 'default');
saveSetting('channel', 'dev');
saveSetting('version', '1.14.4.14');
showmessage('成功更新到 1.14.4.14！', './');
?>