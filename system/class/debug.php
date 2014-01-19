<?php
if (!defined('IN_KKFRAME')) exit();
class DEBUG {
	function INIT() {
		$GLOBALS['debug']['time_start'] = self::getmicrotime();
		$GLOBALS['debug']['query_num'] = 0;
	}
	function getmicrotime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
	function output() {
		$return[] = 'MySQL 请求 '.$GLOBALS['debug']['query_num'].' 次';
		$return[] = '运行时间：'.number_format((self::getmicrotime() - $GLOBALS['debug']['time_start']), 6).'秒';
		return implode(' , ', $return);
	}
	function query_counter() {
		$GLOBALS['debug']['query_num']++;
	}
	function MSG($string) {
		if ($_GET['debug']) echo "{$string}\r\n";
	}
}
