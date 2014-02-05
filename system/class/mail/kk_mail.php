<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

class kk_mail extends mailer{
	var $id = 'kk_mail';
	var $name = 'KK Mailer';
	var $description = 'KK 提供的邮件代理发送邮件 (发送者显示 KK-Open-Mail-System &lt;open_mail_api@ikk.me&gt;)';
	var $config = array();
	function isAvailable(){
		return true;
	}
	function send($mail){
		$result = cloud::request('mail', $mail->address, $mail->subject, $mail->message, VERSION);
		return $result['status'] == 'ok';
	}
}

?>