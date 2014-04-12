<?php
define('IN_ADMINCP', true);
define('DISABLE_CRON', true);
define('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';
$plugin_id = htmlspecialchars($_GET['id']);
$plugin_var = CACHE::get('plugin');
if(!isset($plugin_var[ $plugin_id ])) throw new Exception("Unknown plugin '{$plugin_id}'");
$obj = HOOK::getPlugin($plugin_id);
$obj->handleAction();