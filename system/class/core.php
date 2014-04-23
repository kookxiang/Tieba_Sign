<?php
if (!defined('IN_KKFRAME')) exit();
class core {
	function init() {
		global $_config;
		if(!$_config) require_once SYSTEM_ROOT.'./config.inc.php';
		DEBUG::INIT();
		$this->init_header();
		$this->init_useragent();
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
		HOOK::run('on_load');
	}
	function init_cron() {
		if (defined('ENABLE_CRON')) return;
		$n = mktime(0, 0, 0);
		$p = TIMESTAMP;
		$c = getSetting('next_cron');
		$d = date('Ymd', TIMESTAMP);
		$dd = getSetting('date');
		if ($d != $dd) {
			$r = $n + 1800;
			DB::query("UPDATE cron SET enabled='1', nextrun='{$r}'");
			DB::query("UPDATE cron SET nextrun='{$n}' WHERE id='daily'");
			saveSetting('date', $d);
			saveSetting('next_cron', TIMESTAMP);
			return;
		}
		if ($c > $p) return;
		$t = DB::fetch_first("SELECT * FROM cron WHERE enabled='1' AND nextrun<'{$p}' ORDER BY `order` LIMIT 0,1");
		$s = SYSTEM_ROOT."./function/cron/{$t[id]}.php";
		if (file_exists($s)) {
			include $s;
		} else {
			define('CRON_FINISHED', true);
		}
		if (defined('CRON_FINISHED')) DB::query("UPDATE cron SET enabled='0' WHERE id='{$t[id]}'");
		$r = DB::fetch_first("SELECT nextrun FROM cron WHERE enabled='1' ORDER BY nextrun ASC LIMIT 0,1");
		saveSetting('next_cron', $r ? $r['nextrun'] : TIMESTAMP + 1200);
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
