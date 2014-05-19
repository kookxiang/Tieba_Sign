<?php
if (!defined('IN_KKFRAME')) exit();

class MultiThread {
	public static function registerThread($max_thread = 6, $ttl = 30){
		$time = TIMESTAMP;
		$threadCount = DB::result_first("SELECT COUNT(*) FROM process WHERE exptime >= '{$time}'");
		if($threadCount >= $max_thread) return false;
		DB::query("DELETE FROM process WHERE exptime < '{$time}'");
		$time += $ttl;
		$pid = random(16);
		DB::query("INSERT INTO process SET exptime='{$time}', id='{$pid}'");
		return true;
	}
	public static function newCronThread(){
		global $siteurl, $real_siteurl;
		$url = $real_siteurl ? $real_siteurl : $siteurl;
		$matches = parse_url($url);
		$host = $matches['host'];
		$port = !empty($matches['port']) ? $matches['port'] : 80;
		$path = $matches['path'] ? $matches['path'] : '/';
		$header = "GET {$path}cron.php HTTP/1.0\r\n";
		$header .= "Accept: */*\r\n";
		$header .= "Host: {$host}:{$port}\r\n";
		$header .= "Connection: Close\r\n\r\n";
		$fp = fsocketopen($host, $port);
		if(!$fp) return false;
		stream_set_timeout($fp, 1);
		@fwrite($fp, $header);
		@fgets($fp);
		fclose($fp);
		return true;
	}
}