<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

class kk_mail extends mailer{
	var $id = 'kk_mail';
	var $name = 'KK Mailer';
	var $description = 'KK 提供的 SAE 邮件代理发送邮件 (发送者显示 KK-Open-Mail-System &lt;open_mail_api@ikk.me&gt;)';
	var $config = array(
		array('加密密钥', 'key', '一般请保持默认', 'c131027cf14ed57680ee'),
		array('API地址', 'path', '', 'http://api.ikk.me/mail.php'),
	);
	function isAvailable(){
		return true;
	}
	function send($mail){
		$data = array(
			'to' => $mail->address,
			'title' => $mail->subject,
			'content' => $mail->message,
			'ver' => VERSION,
		);
		$path = authcode(serialize($data), 'ENCODE', $this->_get_setting('key'));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_get_setting('path'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'data='.urlencode($path));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result == 'ok';
	}
}

?>