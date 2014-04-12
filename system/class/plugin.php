<?php
if(!defined('IN_KKFRAME')) exit();
class Plugin {
	var $name;
	var $description;
	var $modules = array();
	var $permission = array();
	var $version = '0';
	function getSetting($key, $default_value = false){
		$vars = CACHE::get('plugin');
		$vars = $vars[ $this->getPluginId() ];
		return isset($vars[$key]) ? $vars[$key] : $default_value;
	}
	function saveSetting($key, $value){
		$pluginid = $this->getPluginId();
		$vars = CACHE::get('plugin');
		if(!$vars) $vars = array();
		if(!$vars[ $pluginid ]) $vars[ $pluginid ] = array();
		$vars[ $pluginid ][ $key ] = $value;
		DB::query("REPLACE INTO plugin_var SET `key` = '".addslashes($key)."', `value` = '".addslashes($value)."', pluginid='".addslashes($pluginid)."'");
		CACHE::clean('plugin');
	}
	function checkCompatibility(){
		return true;
	}
	function install(){
		// install script
	}
	function uninstall(){
		// uninstall script
	}
	function handleAction(){
		throw new Exception('This plugin doesn\'t support to be called directly.');
	}
	private function getPluginId(){
		return str_replace('plugin_', '', get_class($this));
	}
}
