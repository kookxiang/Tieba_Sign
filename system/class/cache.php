<?php
if (!defined('IN_KKFRAME')) exit();
$_CACHE = array();
class CACHE {
	public static function pre_fetch(){
		global $_CACHE;
		if (isset($_CACHE[$key])) return $_CACHE[$key];
		$cache_keys = func_get_args();
		$query = DB::query("SELECT * FROM cache WHERE k IN ('".implode("', '", $cache_keys)."')", 'SILENT');
		while($cache = DB::fetch($query)){
			$key = $cache['k'];
			$value = $cache['v'];
			$arr = @unserialize($value);
			$_CACHE[$key] = $arr !== FALSE ? $arr : $value;
		}
	}
	public static function get($key) {
		global $_CACHE;
		if (isset($_CACHE[$key])) return $_CACHE[$key];
		$query = DB::query("SELECT v FROM cache WHERE k='{$key}'", 'SILENT');
		$result = DB::fetch($query);
		$arr = @unserialize($result['v']);
		$_CACHE[$key] = $arr !== FALSE ? $arr : $result['v'];
		if (!$_CACHE[$key]) {
			return $_CACHE[$key] = self::update($key);
		}
		return $_CACHE[$key];
	}
	public static function save($key, $value) {
		if (is_array($value)) $value = serialize($value);
		$value = addslashes($value);
		DB::query("REPLACE INTO cache SET k='{$key}', v='{$value}'", 'SILENT');
	}
	public static function update($key) {
		$builder_file = SYSTEM_ROOT."./function/cache/cache_{$key}.php";
		if (file_exists($builder_file)) {
			$cache = array();
			include $builder_file;
			self::save($key, $cache);
			return $cache;
		}
	}
	public static function clean($key) {
		DB::query("DELETE FROM cache WHERE k='{$key}'", 'SILENT');
	}
	public static function clear() {
		DB::query("TRUNCATE TABLE cache", 'SILENT');
	}
}
