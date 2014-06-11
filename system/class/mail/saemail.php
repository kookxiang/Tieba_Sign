<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

class saemail extends mailer{
	var $id = 'saemail';
	var $name = 'SAE SMTP 类';
	var $description = 'SAE 用户可用，通过 SAE 的 SMTP 类发送邮件';
	var $config = array(
		array('SMTP 服务器地址', 'smtp_server', '', ''),
		array('发送者邮箱地址', 'address', '', '', 'email'),
		array('SMTP 用户名', 'smtp_name', '', ''),
		array('SMTP 密码', 'smtp_pass', '', '', 'password'),
	);
	function isAvailable(){
		return defined('IN_SAE');
	}
	function send($mail){
		$saemail = new SaeMail();
		$saemail->setOpt(array(
			'from' => '贴吧签到助手 <'.$this->_get_setting('address').'>',
			'to' => $mail->address,
			'smtp_host' => $this->_get_setting('smtp_server'),
			'smtp_username' => $this->_get_setting('smtp_name'),
			'smtp_password' => $this->_get_setting('smtp_pass'),
			'subject' => $mail->subject,
			'content' => $mail->message,
			'content_type' => 'HTML',
		));
		$saemail->send();
		return true;
	}
}

?>