<?php
if (!defined('IN_KKFRAME')) exit();
class db_mysql {
	var $curlink;
	var $last_query;
	function connect() {
		global $_config;
		$this->curlink = $this->_dbconnect($_config['db']['server'].':'.$_config['db']['port'], $_config['db']['username'], $_config['db']['password'], 'utf8', $_config['db']['name'], $_config['db']['pconnect']);
	}
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		$link = null;
		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if (!$link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$this->halt('Couldn\'t connect to MySQL Server');
		} else {
			$this->curlink = $link;
			if ($this->version() > '4.1') {
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $link);
			}
			$dbname && @mysql_select_db($dbname, $link);
		}
		return $link;
	}
	function select_db($dbname) {
		return mysql_select_db($dbname, $this->curlink);
	}
	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}
	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}
	function query($sql, $type = '') {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if (!$this->curlink) $this->connect();
		if (!($query = $func($sql, $this->curlink))) {
			if ($type != 'SILENT') {
				$this->halt('MySQL Query ERROR', $sql);
			}
		}
		return $this->last_query = $query;
	}
	function affected_rows() {
		return mysql_affected_rows($this->curlink);
	}
	function error() {
		return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
	}
	function errno() {
		return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
	}
	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}
	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}
	function num_fields($query) {
		return mysql_num_fields($query);
	}
	function free_result($query) {
		return mysql_free_result($query);
	}
	function insert_id() {
		return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}
	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
	function version() {
		if (empty($this->version)) {
			$this->version = mysql_get_server_info($this->curlink);
		}
		return $this->version;
	}
	function close() {
		return mysql_close($this->curlink);
	}
	function halt($message = '', $sql = '') {
		kerror::db_error($message, $sql);
	}
	function __destruct() {
		$this->close();
	}
}

class db_mysqli {
	var $curlink;
	var $last_query;
	function connect() {
		global $_config;
		$this->curlink = $this->_dbconnect($_config['db']['server'].':'.$_config['db']['port'], $_config['db']['username'], $_config['db']['password'], 'utf8', $_config['db']['name'], $_config['db']['pconnect']);
	}
	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect) {
		$link = null;
		if (!$link = mysqli_connect($dbhost, $dbuser, $dbpw)) {
			$this->halt('Couldn\'t connect to MySQL Server');
		} else {
			$this->curlink = $link;
			$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary,sql_mode=\'\'' : '';
			$serverset && mysqli_query($link, "SET $serverset");
			$dbname && @mysqli_select_db($link, $dbname);
		}
		return $link;
	}
	function select_db($dbname) {
		return mysqli_select_db($this->curlink, $dbname);
	}
	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		return mysqli_fetch_array($query, $result_type);
	}
	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}
	function query($sql, $type = '') {
		if (!$this->curlink) $this->connect();
		if (!($query = mysqli_query($this->curlink, $sql))) {
			if ($type != 'SILENT') {
				$this->halt('MySQL Query ERROR', $sql);
			}
		}
		return $this->last_query = $query;
	}
	function affected_rows() {
		return mysqli_affected_rows($this->curlink);
	}
	function error() {
		return (($this->curlink) ? mysqli_error($this->curlink) : mysqli_error());
	}
	function errno() {
		return intval(($this->curlink) ? mysqli_errno($this->curlink) : mysqli_errno());
	}
	function result($query, $row = 0) {
		$query = mysqli_fetch_row($query)[$row];
		return $query;
	}
	function num_rows($query) {
		$query = mysqli_num_rows($query);
		return $query;
	}
	function num_fields($query) {
		return mysqli_num_fields($query);
	}
	function free_result($query) {
		return mysqli_free_result($query);
	}
	function insert_id() {
		return ($id = mysqli_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	function fetch_row($query) {
		$query = mysqli_fetch_row($query);
		return $query;
	}
	function fetch_fields($query) {
		return mysqli_fetch_field($query);
	}
	function version() {
		if (empty($this->version)) {
			$this->version = mysqli_get_server_info($this->curlink);
		}
		return $this->version;
	}
	function close() {
		return mysqli_close($this->curlink);
	}
	function halt($message = '', $sql = '') {
		kerror::db_error($message, $sql);
	}
	function __destruct() {
		$this->close();
	}
}

class DB {
	public static function delete($table, $condition, $limit = 0, $unbuffered = true) {
		if (empty($condition)) {
			$where = '1';
		} elseif (is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$sql = "DELETE FROM {$table} WHERE $where ".($limit ? "LIMIT $limit" : '');
		return DB::query($sql, ($unbuffered ? 'UNBUFFERED' : ''));
	}
	public static function insert($table, $data, $return_insert_id = true, $replace = false, $silent = false) {
		$sql = DB::implode_field_value($data);
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$silent = $silent ? 'SILENT' : '';
		$return = DB::query("$cmd $table SET $sql", $silent);
		return $return_insert_id ? DB::insert_id() : $return;
	}
	public static function update($table, $data, $condition, $unbuffered = false, $low_priority = false) {
		$sql = DB::implode_field_value($data);
		$cmd = "UPDATE ".($low_priority ? 'LOW_PRIORITY' : '');
		$where = '';
		if (empty($condition)) {
			$where = '1';
		} elseif (is_array($condition)) {
			$where = DB::implode_field_value($condition, ' AND ');
		} else {
			$where = $condition;
		}
		$res = DB::query("$cmd $table SET $sql WHERE $where", $unbuffered ? 'UNBUFFERED' : '');
		return $res;
	}
	public static function implode_field_value($array, $glue = ',') {
		$sql = $comma = '';
		foreach ($array as $k => $v) {
			$sql .= $comma."`$k`='$v'";
			$comma = $glue;
		}
		return $sql;
	}
	public static function insert_id() {
		return DB::_execute('insert_id');
	}
	public static function fetch($resourceid, $type = MYSQLI_ASSOC) {
		return DB::_execute('fetch_array', $resourceid, $type);
	}
	public static function fetch_first($sql) {
		return DB::_execute('fetch_first', $sql);
	}
	public static function fetch_all($sql) {
		$query = DB::_execute('query', $sql);
		$return = array();
		while ($result = DB::fetch($query)) {
			$return[] = $result;
		}
		return $return;
	}
	public static function result($resourceid, $row = 0) {
		return DB::_execute('result', $resourceid, $row);
	}
	public static function result_first($sql) {
		return DB::_execute('result_first', $sql);
	}
	public static function query($sql, $type = '') {
		return DB::_execute('query', $sql, $type);
	}
	public static function num_rows($resourceid) {
		return DB::_execute('num_rows', $resourceid);
	}
	public static function affected_rows() {
		return DB::_execute('affected_rows');
	}
	public static function free_result($query) {
		return DB::_execute('free_result', $query);
	}
	public static function error() {
		return DB::_execute('error');
	}
	public static function errno() {
		return DB::_execute('errno');
	}
	private static function _execute($cmd , $arg1 = '', $arg2 = '') {
		static $db;
		if (empty($db)) $db = &DB::object();
		$res = $db->$cmd($arg1, $arg2);
		return $res;
	}
	public static function &object() {
		static $db;
		if (empty($db)) {
			if (function_exists('mysql_connect')) {
				$db = new db_mysql();
			} else {
				$db = new db_mysqli();
			}
		}
		return $db;
	}
}
