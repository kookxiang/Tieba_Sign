<?php
if (!defined('IN_KKFRAME')) exit();
class HOOK {
	function INIT() {
		global $_PLUGIN;
		$_PLUGIN = array();
		if (defined('DISABLE_PLUGIN')) return;
		$_PLUGIN['list'] = CACHE::get('plugins');
		$_PLUGIN['obj'] = array();
		$_PLUGIN['hook'] = array();
		$_PLUGIN['page'] = array();
		foreach($_PLUGIN['list'] as $plugin) {
			$pluginid = $plugin['id'];
			$classfile = ROOT.'./plugins/'.$pluginid.'/plugin.class.php';
			if (file_exists($classfile)) {
				require_once $classfile;
				$classname = "plugin_{$pluginid}";
				if (!class_exists("plugin_{$pluginid}", false)) continue;
				$_PLUGIN['obj'][$pluginid] = new $classname();
				$methods = get_class_methods($classname);
				if (property_exists($_PLUGIN['obj'][$pluginid], 'version')) {
					$version = $_PLUGIN['obj'][$pluginid]->version;
					if ($plugin['ver'] != $version) {
						DB::query("UPDATE `plugin` SET `version`='{$version}' WHERE name='{$pluginid}'");
						CACHE::update('plugins');
						if (method_exists($_PLUGIN['obj'][$pluginid], 'on_upgrade')) $_PLUGIN['obj'][$pluginid]->on_upgrade($plugin['ver']);
					}
				}
				foreach ($methods as $method) $_PLUGIN['hook'][$method][] = $pluginid;
				if (method_exists($_PLUGIN['obj'][$pluginid], 'getMethods')) $_PLUGIN['obj'][$pluginid]->modules = $_PLUGIN['obj'][$pluginid]->getMethods();
				if (method_exists($_PLUGIN['obj'][$pluginid], 'getModules')) $_PLUGIN['obj'][$pluginid]->modules = $_PLUGIN['obj'][$pluginid]->getModules();
				foreach ($_PLUGIN['obj'][$pluginid]->modules as $module) self::parse_module($module, $pluginid);
			}
		}
	}
	function parse_module($module, $pluginid) {
		global $_PLUGIN;
		switch ($module['type']) {
			case 'page': $_PLUGIN['page'][] = array('id' => "{$pluginid}-{$module[id]}", 'title' => $module['title'], 'file' => ROOT."./plugins/{$pluginid}/".$module['file'], 'admin' => $module['admin']);
				break;
			default: throw new Exception('Unknown module type: '.$module['type']);
		}
	}
	function page_menu() {
		global $_PLUGIN, $uid;
		foreach ($_PLUGIN['page'] as $page) {
			if ($page['admin'] && !is_admin($uid)) continue;
			echo "<li id=\"menu_{$page[id]}\"><a href=\"#{$page[id]}\">{$page[title]}</a></li>";
		}
	}
	function page_contents() {
		global $_PLUGIN, $uid;
		foreach($_PLUGIN['page'] as $page) {
			if ($page['admin'] && !is_admin($uid)) continue;
			echo "<div id=\"content-{$page[id]}\" class=\"hidden\">";
			@include $page['file'];
			echo "</div>\r\n";
		}
	}
	function run($hookname) {
		global $_PLUGIN;
		if (defined('DISABLE_PLUGIN')) return;
		$hooks = $_PLUGIN['hook'][$hookname];
		if (!$hooks) return;
		$args = func_get_args();
		foreach($hooks as $pluginid) {
			try {
				echo $_PLUGIN['obj'][$pluginid]->$hookname($args);
			}
			catch(Exception $e) {
				error::exception_error($e);
			}
		}
	}
}
