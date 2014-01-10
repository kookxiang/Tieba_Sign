<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

class phpmail extends mailer{
	var $id = 'phpmail';
	var $name = 'PHP Mail()';
	var $description = '通过 PHP 的 Mail() 函数发送邮件';
	var $config = array();
	function isAvailable(){
		return !isset($_SERVER['HTTP_APPVERSION']) && $_SERVER['USER'] != 'bae';
	}
	function send($mail){
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html;charset=utf-8\r\n";
		$headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
		$headers .= "To: {$address}\r\n";
		$headers .= 'From: =?UTF-8?B?'.base64_encode('贴吧签到助手')."?=\r\n";
		$message = quoted_printable_encode($message);
		return mail($mail->address, '=?UTF-8?B?'.base64_encode($mail->subject).'?=', $mail->message, $headers);
	}
}

?>