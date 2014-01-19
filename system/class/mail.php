<?php
if (!defined('IN_KKFRAME')) exit();
class mailer {
	var $_setting;
	function isAvailable() {
		return false;
	}
	function send() {
		return false;
	}
	function _get_setting($key) {
		if (!$this->_setting) $this->_load_setting();
		return $this->_setting[$key];
	}
	function _load_setting() {
		$this->_setting = CACHE::get('mail_'.$this->id);
		if ($this->_setting) return;
		$this->_setting = array();
		if ($this->config) {
			foreach($this->config as $k => $v) {
				$this->_setting[ $v[1] ] = $v[3];
			}
		}
		$class = getSetting('mail_class');
		$query = DB::query("SELECT * FROM setting WHERE k LIKE '_mail_{$class}_%'");
		while ($result = DB::fetch($query)) {
			$key = str_replace("_mail_{$class}_", '', $result['k']);
			$this->_setting[$key] = $result['v'];
		}
		CACHE::save('mail_'.$this->id, $this->_setting);
	}
}
class mail_content {
	var $address;
	var $subject;
	var $message;
}
class mail_sender {
	var $obj;
	function __construct() {
		$sender = getSetting('mail_class');
		$file = SYSTEM_ROOT."./class/mail/{$sender}.php";
		if (file_exists($file)) {
			require_once $file;
			$this->obj = new $sender();
		}
	}
	function sendMail($mail) {
		if (!$this->obj) return false;
		return $this->obj->send($mail);
	}
}
