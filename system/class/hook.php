<?php
if(!defined('IN_KKFRAME')) exit();
class HOOK{
	function INIT(){
		global $_PLUGIN;
		$_PLUGIN = array();
		$_PLUGIN['list'] = CACHE::get('plugins');
		$_PLUGIN['obj'] = array();
		$_PLUGIN['hook'] = array();
		$_PLUGIN['page'] = array();
		$_PLUGIN['shortcut'] = array();
		foreach($_PLUGIN['list'] as $plugin){
			$pluginid = $plugin['id'];
			$classfile = ROOT.'./plugins/'.$pluginid.'/plugin.class.php';
			if(file_exists($classfile)){
				require_once $classfile;
				$classname = "plugin_{$pluginid}";
				if(!class_exists("plugin_{$pluginid}", false)) continue;
				$_PLUGIN['obj'][$pluginid] = new $classname();
				if(method_exists($obj, '__construct') || method_exists($obj, '__destruct') || method_exists($obj, $classname)){
					unset($_PLUGIN['obj'][$pluginid]);
					continue;
				}
				$methods = get_class_methods($classname);
				if(property_exists($_PLUGIN['obj'][$pluginid], 'version')){
					$version = $_PLUGIN['obj'][$pluginid]->version;
					if($version && $plugin['ver'] != $version){
						if(method_exists($_PLUGIN['obj'][$pluginid], 'on_upgrade')){
							$return_ver = $_PLUGIN['obj'][$pluginid]->on_upgrade($plugin['ver']);
							if($return_ver){
								DB::query("UPDATE `plugin` SET `version`='{$return_ver}' WHERE name='{$pluginid}'");
							}else{
								DB::query("UPDATE `plugin` SET `version`='{$version}' WHERE name='{$pluginid}'");
							}
						}else{
							DB::query("UPDATE `plugin` SET `version`='{$version}' WHERE name='{$pluginid}'");
						}
						// Reload cron scripts
						DB::query("DELETE FROM cron WHERE id LIKE '%".$pluginid."%'");
						foreach($_PLUGIN['obj'][$pluginid]->modules as $module){
							if($module['type'] == 'cron'){
								DB::insert('cron', array_merge($module['cron'], array('nextrun' => TIMESTAMP)), false, true);
							}
						}
						CACHE::update('plugins');
					}
				}
				foreach ($methods as $method) $_PLUGIN['hook'][$method][] = $pluginid;
				if(method_exists($_PLUGIN['obj'][$pluginid], 'getMethods')) $_PLUGIN['obj'][$pluginid]->modules = $_PLUGIN['obj'][$pluginid]->getMethods();
				if(method_exists($_PLUGIN['obj'][$pluginid], 'getModules')) $_PLUGIN['obj'][$pluginid]->modules = $_PLUGIN['obj'][$pluginid]->getModules();
				foreach ($_PLUGIN['obj'][$pluginid]->modules as $module) self::parse_module($module, $pluginid);
			}
		}
	}
	function parse_module($module, $pluginid){
		global $_PLUGIN;
		switch ($module['type']){
			case 'page':
				$_PLUGIN['page'][] = array(
					'id' => "{$pluginid}-{$module[id]}",
					'title' => $module['title'],
					'file' => ROOT."./plugins/{$pluginid}/".$module['file'],
					'admin' => $module['admin'],
					);
				break;
			case 'shortcut':
				$_PLUGIN['shortcut'][] = array(
					'title' => $module['title'],
					'link' => $module['link'],
					'admin' => $module['admin'],
					);
				break;
			case 'cron':
				break;
			default: throw new Exception('Unknown module type: '.$module['type']);
		}
	}
	function page_menu(){
		global $_PLUGIN, $uid;
		if($_PLUGIN['page']){
			foreach ($_PLUGIN['page'] as $page){
				if($page['admin'] && !is_admin($uid)) continue;
				echo "<li id=\"menu_{$page[id]}\"><a href=\"#{$page[id]}\">{$page[title]}</a></li>";
			}
		}
		if($_PLUGIN['shortcut']){
			foreach ($_PLUGIN['shortcut'] as $page){
				if($page['admin'] && !is_admin($uid)) continue;
				echo "<li><a href=\"{$page[link]}\">{$page[title]}</a></li>";
			}
		}
	}
	function page_contents(){
		global $_PLUGIN, $uid;
		if($_PLUGIN['page']){
			foreach($_PLUGIN['page'] as $page){
				if($page['admin'] && !is_admin($uid)) continue;
				echo "<div id=\"content-{$page[id]}\" class=\"hidden\">";
				@include $page['file'];
				echo "</div>\r\n";
			}
		}
	}
	function run($hookname, $ignore_unabled = false){
		global $_PLUGIN;
		if(defined('DISABLE_PLUGIN') && !$ignore_unabled) return;
		$hooks = $_PLUGIN['hook'][$hookname];
		if(!$hooks) return;
		$args = func_get_args();
		unset($args[0], $args[1]);
		foreach($hooks as $pluginid){
			try{
				echo call_user_func_array(array(&$_PLUGIN['obj'][$pluginid], $hookname), $args);
			}catch(Exception $e){
				error::exception_error($e);
			}
		}
	}
	function getPlugin($plugin_id){
		global $_PLUGIN;
		if($_PLUGIN['obj'][$plugin_id]){
			return $_PLUGIN['obj'][$plugin_id];
		}elseif(defined('DISABLE_PLUGIN')){
			$classname = 'plugin_'.$plugin_id;
			return $_PLUGIN['obj'][$plugin_id] = new $classname();
		}
	}
}
