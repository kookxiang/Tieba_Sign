<?php
if (!defined('IN_KKFRAME')) exit();
class core {
	function init() {
		global $_config;
		if(!$_config) require_once SYSTEM_ROOT.'./config.inc.php';
		DEBUG::INIT();
		$this->init_header();
		$this->init_useragent();
		CACHE::pre_fetch('setting', 'plugin', 'plugins');
		Updater::init();
		$this->init_syskey();
		$this->init_cookie();
		cloud::init();
		HOOK::INIT();
		$this->init_final();
	}
	function __destruct() {
		if (!defined('SYSTEM_STARTED')) return;
		HOOK::run('on_unload');
		flush();
		ob_end_flush();
		$this->init_cron();
		$this->init_mail();
	}
	function init_header() {
		ob_start();
		header('Content-type: text/html; charset=utf-8');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		@date_default_timezone_set('Asia/Shanghai');
	}
	function init_useragent() {
		$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
		if (strpos($ua, 'wap') || strpos($ua, 'mobi') || strpos($ua, 'opera') || $_GET['mobile']) {
			define('IN_MOBILE', true);
		} else {
			define('IN_MOBILE', false);
		}
		if (strpos($ua, 'bot') || strpos($ua, 'spider')) define('IN_ROBOT', true);
	}
	function init_syskey() {
		define('ENCRYPT_KEY', getSetting('SYS_KEY'));
	}
	function init_cookie() {
		global $cookiever, $uid, $username;
		$cookiever = '2';
		if (!empty($_COOKIE['token'])) {
			list($cc, $uid, $username, $exptime, $password) = explode("\t", authcode($_COOKIE['token'], 'DECODE'));
			if (!$uid || $cc != $cookiever) {
				unset($uid, $username, $exptime);
				dsetcookie('token');
			} elseif ($exptime < TIMESTAMP) {
				$user = DB::fetch_first("SELECT * FROM member WHERE uid='{$uid}'");
				$_password = substr(md5($user['password']), 8, 8);
				if ($user && $password == $_password) {
					$exptime = TIMESTAMP + 900;
					dsetcookie('token', authcode("{$cookiever}\t{$uid}\t{$user[username]}\t{$exptime}\t{$password}", 'ENCODE'));
				} else {
					unset($uid, $username, $exptime);
					dsetcookie('token');
				}
			}
		} else {
			$uid = $username = '';
		}
	}
	function init_final() {
		define('SYSTEM_STARTED', true);
		@ignore_user_abort(true);
		if(getSetting('AFENABLED')) define('AFENABLED', true);
		HOOK::run('on_load');
	}
	function init_cron() {
		if (!defined('ENABLE_CRON')) return;
		$today = mktime(0, 0, 0);
		$tomorrow = $today + 86400;
		$nowtime = TIMESTAMP;
		$cron_next_run = getSetting('next_cron');
		if ($cron_next_run > $nowtime) return;
		$cron = DB::fetch_first("SELECT * FROM cron WHERE nextrun<'{$nowtime}' ORDER BY `order` LIMIT 0,1");
		define('CRON_ID', $cron['id']);
		list($pluginid, $cronscript) = explode('/', CRON_ID, 2);
		if($pluginid && $cronscript){
			$script_path = ROOT."./plugins/{$pluginid}/{$cronscript}.cron.php";
		}else{
			$script_path = SYSTEM_ROOT.'./function/cron/'.CRON_ID.'.php';
		}
		if (file_exists($script_path)) {
			include $script_path;
			if (defined('CRON_FINISHED')) cron_set_nextrun($tomorrow + 1800);
		} else {
			cron_set_nextrun($tomorrow + 1800);
		}
	}
	function init_mail() {
		if (defined('DISABLE_CRON')) return;
		$queue = getSetting('mail_queue');
		if (!$queue) return;
		$mail = DB::fetch_first("SELECT * FROM mail_queue LIMIT 0,1");
		if ($mail) {
			DB::query("DELETE FROM mail_queue WHERE id='{$mail[id]}'");
			$_mail = new mail_content();
			$_mail->address = $mail['to'];
			$_mail->subject = $mail['subject'];
			$_mail->message = $mail['content'];
			$sender = new mail_sender();
			$sender->sendMail($_mail);
		} else {
			saveSetting('mail_queue', 0);
		}
	}
}
