<?php
require_once './system/common.inc.php';
if(!$uid) exit('Access Denied');

$data = array();
switch($_GET['v']){
	case 'liked_tieba':
		$query = DB::query("SELECT * FROM my_tieba WHERE uid='{$uid}'");
		while($result = DB::fetch($query)){
			$data[] = $result;
		}
		break;
	case 'get-bind-status':
		$data = get_baidu_userinfo($uid);
		if($data['data']['user_portrait']) dsetcookie("avatar_{$uid}", 'http://tb.himg.baidu.com/sys/portrait/item/'.$data['data']['user_portrait']);
		break;
	case 'get-setting':
		$data = get_setting($uid);
		break;
	case 'sign-log':
		$date = date('Ymd');
		$data['date'] = date('Y-m-d');
	case 'sign-history':
		if($_GET['v'] == 'sign-history'){
			$date = intval($_GET['date']);
			$data['date'] = substr($date, 0, 4).'-'.substr($date, 4, 2).'-'.substr($date, 6, 2);
		}
		$data['log'] = array();
		$query = DB::query("SELECT * FROM sign_log l LEFT JOIN my_tieba t ON t.tid=l.tid WHERE l.uid='{$uid}' AND l.date='{$date}'");
		while($result = DB::fetch($query)){
			$data['log'][] = $result;
		}
		$data['count'] = count($data['log']);
		$data['before_date'] = DB::result_first("SELECT date FROM sign_log WHERE uid='{$uid}' AND date<'{$date}' ORDER BY date DESC LIMIT 0,1");
		$data['after_date'] = DB::result_first("SELECT date FROM sign_log WHERE uid='{$uid}' AND date>'{$date}' ORDER BY date ASC LIMIT 0,1");
		break;
}
echo json_encode($data);