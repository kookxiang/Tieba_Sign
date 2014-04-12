<?php
define('IN_ADMINCP', true);
define('DISABLE_CRON', true);
define('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';
$plugin_id = $_GET['id'];
$plugin_var = CACHE::get('plugin');
$vars = $plugin_var[ $plugin_id ];
if(!$vars) throw new Exception("Unknown plugin <{$plugin_id}>");
$obj = HOOK::getPlugin($plugin_id);
$obj->handleAction();