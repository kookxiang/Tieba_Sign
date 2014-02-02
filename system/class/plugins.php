<?php
if (! defined ( 'IN_KKFRAME' ))
	exit ();
class plugins { // 插件模型，继承此模型可内置一些插件操作方法
	var $name;
	var $id;
	var $description = '该插件无描述';
	var $modules = array ();
	var $version = '0.0.0';
	var $autoGetSetting = false; // 自动加载插件设置
	var $settings;
	function __construct() { // 实例时进行一系列初始化操作
		if ($this->name == null || $this->name == '')
			return false; // 如果没有声明插件name则返回
		$this->id = $this->getPluginId ();
		if ($this->autoGetSetting === true && $this->id != null && $this->id != 0) {
			$query = DB::query ( "SELECT k,v FROM `plugin_setting` WHERE id={$this->id};" );
			while ( $result = DB::fetch ( $query ) ) {
				$this->settings [$result ['k']] = $result ['v'];
			}
			$query = DB::query ( "SELECT k,v FROM `plugin_setting_b` WHERE id={$this->id};" );
			while ( $result = DB::fetch ( $query ) ) {
				$this->settings [$result ['k']] = $result ['v'];
			}
		}
		if (method_exists ( $this, 'init' ))
			$this->init (); // 执行init方法，用于插件初始化
	}
	protected function setting($k, $b = false, $v = null) { // b为true则保存到/读取自大容量表plugin_setting_b
		$tableName = $b == false ? 'plugin_setting' : 'plugin_setting_b';
		$settings = DB::result_first ( "SELECT v FROM {$tableName} WHERE k='{$k}' AND id={$this->id}" );
		if ($v === null) { // 如果v为空则读取，否则存储
			return $settings;
		} else {
			if ($v !== "") { // 值不为空则进行设置，否则删除
				if ($settings == null) { // 如果不存在则插入
					return DB::insert ( $tableName, array (
							'id' => $this->id,
							'k' => mysql_escape_string ( $k ),
							'v' => mysql_escape_string ( $v ) 
					) );
				} else { // 如果存在则更新
					return DB::update ( $tableName, array (
							'v' => mysql_escape_string ( $v ) 
					), array (
							'id' => $this->id,
							'k' => mysql_escape_string ( $k ) 
					) );
				}
			} else {
				return DB::query ( "DELETE FROM " . $tableName . " WHERE id=" . $this->id . " AND `k`='" . $k . "'" );
			}
		}
	}
	function getPluginId($name = null) { // 获取插件ID
		if ($name == null)
			$name = $this->name;
		$pluginlist = CACHE::get ( 'plugins' );
		foreach ( $pluginlist as $plugin ) {
			if ($plugin ['id'] == $name) { // 注：系统变量内的id非方法所找“id”之意，而是这里的name
				$findver = $plugin ['ver'];
			}
		}
		$findarray = array (
				'id' => $name,
				'ver' => $findver 
		);
		$key = array_keys ( $pluginlist, $findarray );
		return $key [0];
	}
}