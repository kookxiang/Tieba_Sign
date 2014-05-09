<?php
if(!defined('IN_KKFRAME')) exit();

cron_set_nextrun($tomorrow + 21600);
$_uid = 1;
while($_uid){
	$user = DB::fetch_first("SELECT uid, username, email FROM member WHERE uid='{$_uid}'");
	if(check_if_msg($user)) sendmsg($user);
	$_uid = DB::result_first("SELECT uid FROM member WHERE uid>'{$_uid}' ORDER BY uid ASC LIMIT 0,1");
}
saveSetting('mail_queue', 1);

function check_if_msg($user){
	$date = date('Ymd', TIMESTAMP);
	$uid = $user['uid'];
	$total_num = DB::result_first("SELECT COUNT(*) FROM sign_log WHERE date='{$date}' AND uid='{$uid}'");
	if($total_num > 200) return false;
	$setting = get_setting($user['uid']);
	if($setting['send_mail']) return true;
	if(!$setting['error_mail']) return false;
	$error_num = DB::result_first("SELECT COUNT(*) FROM sign_log WHERE status!='2' AND status!='-2' AND date='{$date}' AND uid='{$uid}'");
	if($error_num > 0) return true;
}
function sendmsg($user){
	$date = date('Ymd', TIMESTAMP);
	$mdate = date('Y-m-d', TIMESTAMP);
	$uid = $user['uid'];
	$log = array();
	$query = DB::query("SELECT * FROM sign_log l LEFT JOIN my_tieba t ON t.tid=l.tid WHERE l.uid='{$uid}' AND l.date='{$date}' ORDER BY l.status DESC, l.tid ASC");
	$i = 1;
	$message = <<<EOF
<html><body>
<style type="text/css">
div.wrapper * { font: 12px "Microsoft YaHei", arial, helvetica, sans-serif; word-break: break-all; }
div.wrapper a { color: #15c; text-decoration: none; }
div.wrapper a:active { color: #d14836; }
div.wrapper a:hover { text-decoration: underline; }
div.wrapper p { line-height: 20px; margin: 0 0 .5em; text-align: center; }
div.wrapper .sign_title { font-size: 20px; line-height: 24px; }
div.wrapper .result_table { width: 85%; margin: 0 auto; border-spacing: 0; border-collapse: collapse; }
div.wrapper .result_table td { padding: 10px 5px; text-align: center; border: 1px solid #dedede; }
div.wrapper .result_table tr { background: #d5d5d5; }
div.wrapper .result_table tbody tr { background: #efefef; }
div.wrapper .result_table tbody tr:nth-child(odd) { background: #fafafa; }
</style>
<div class="wrapper">
<p class="sign_title">贴吧签到助手 - 签到报告</p>
<p>{$mdate}<br>若有大量贴吧签到失败，建议您重新设置 Cookie 相关信息</p>
<table class="result_table">
<thead><tr><td style="width: 40px">#</td><td>贴吧</td><td style="width: 75px">状态</td><td style="width: 75px">经验</td></tr></thead>
<tbody>
EOF;
	while($result = DB::fetch($query)){
		$message .= '<tr><td>'.($i++)."</td><td><a href=\"http://tieba.baidu.com/f?kw={$result[unicode_name]}\" target=\"_blank\">{$result[name]}</a></td><td>"._status($result['status']).'</td><td>'._exp($result['exp']).'</td></tr>';
		$log[] = $result;
	}
	$message .= '</tbody></table></div></body></html>';
	DB::insert('mail_queue', array(
		'to' => $user['email'],
		'subject' => "[{$mdate}] 贴吧签到助手 - {$user[username]} - 签到报告",
		'content' => $message,
		));
}
function _status($status){
	switch($status){
		case -2:	return '跳过签到';
		case -1:	return '无法签到';
		case 0:		return '待签到';
		case 1:		return '签到失败';
		case 2:		return '已签到';
	}
}
function _exp($exp){
	return $exp == 0 ? '-' : '+'.$exp;
}
