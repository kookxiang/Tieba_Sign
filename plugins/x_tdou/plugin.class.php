<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_x_tdou extends Plugin {
	var $description = '贴吧自动领取豆票并自动砸蛋';
	var $modules = array(
		array('id' => 'log', 'type' => 'page', 'title' => '豆票获取记录', 'file' => 'index.php'),
		array('type' => 'cron', 'cron' => array('id' => 'x_tdou/cron/daily', 'order' => '72')),
		array('type' => 'cron', 'cron' => array('id' => 'x_tdou/cron/get', 'order' => '73')),
	);
	var $version = '0.2.0';
	var $update_time = '2014-05-04';
	public function install() {
		DB::query("CREATE TABLE IF NOT EXISTS `x_tdou_log` (`uid` int(10) unsigned NOT NULL, `date` int(11) NOT NULL DEFAULT '0', `nextrun` int(10) unsigned NOT NULL DEFAULT '0', `num` int(4) NOT NULL DEFAULT '0', `retry` tinyint(1) NOT NULL DEFAULT '0', UNIQUE KEY `uid`(`uid`, `date`)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
	}
	public function uninstall() {
		DB::query("DROP TABLE x_tdou_log");
	}
	public function on_upgrade($oldVersion){
		switch($oldVersion){
			default:
				// 兼容旧版
				DB::query("DELETE FROM cron WHERE id LIKE '%x_tdou%'");
				foreach($this->modules as $module){
					if($module['type'] == 'cron'){
						DB::insert('cron', array_merge($module['cron'], array('nextrun' => TIMESTAMP)), false, true);
					}
				}
		}
	}
	function checkCompatibility(){
		if(version_compare(VERSION, '1.14.4.24', '<')) showmessage('签到助手版本过低，请升级');
	}
	function x_tdou_time($uid){
		$formdata = array(
			'ie' => 'utf-8',
			'tbs' => get_tbs($uid),
			'fr' => 'frs'
		);
		$ch = curl_init('http://tieba.baidu.com/tbscore/timebeat');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formdata));
		curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
		$re = curl_exec($ch);
		curl_close($ch);
		$re = json_decode($re, true);
		$retime = $re['data']['time_stat'];
		if($retime['interval_begin_time'] + $retime['time_len'] < $retime['now_time'] && $retime['time_has_score'] == 'true'){
			$formdata=array(
				'ie' => 'utf-8',
				'tbs' => get_tbs($uid),
			);
			$ch = curl_init('http://tieba.baidu.com/tbscore/fetchtg');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formdata));
			curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
			$re = curl_exec($ch);
			curl_close($ch);
			$re = json_decode($re, true);
		}
		$gift_info = $re['data']['gift_info'][0];
		if($gift_info){
			$get_re = $this->x_tdou_get($gift_info['gift_key'], $gift_info['gift_type'], $uid);
			$score = $get_re['data']['gift_got']['gift_score'];
			if(!$score) $score = 0;
			return array($gift_info['gift_type'], $score);
		}else if(!$re['data']['time_stat']['time_has_score']){
			return array(3, 0);
		}else{
			return array(4, 0);
		}
	}

	function x_tdou_get($gift_key, $type, $uid){
		if($type==1) $type='time';
		elseif ($type==2) $type='rand';
		$formdata=array(
			'ie' => 'utf-8',
			'type' => $type,
			'tbs' => get_tbs($uid),
			'gift_key' => $gift_key
		);
		$ch=curl_init('http://tieba.baidu.com/tbscore/opengift');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formdata));
		curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
		$re = curl_exec($ch);
		curl_close($ch);
		$re = json_decode($re, true);
		return $re;
	}
	function handleAction(){
		global $uid;
		if(!$uid) return;
		switch($_GET['action']) {
		case 'show_log':
			$query = DB::query("select * from x_tdou_log where uid='$uid' order by date desc");
			while($result = DB::fetch($query)){
				$data['logs'][] = $result;
			}
			$data['count'] = count($data['logs']);
			break;
		case 'test' :
			list($statue, $score) = $this->x_tdou_time($uid);
			$date = date('Ymd', TIMESTAMP);
			switch($statue){
				case '1':
					$this->updateStat($score, $uid, $date);
					showmessage("领取在线奖励, 获得 {$score} 个豆票");
					break;
				case '2':
					$this->updateStat($score, $uid, $date);
					showmessage("开彩蛋, 获得 {$score} 个豆票");
					break;
				case '3':
					showmessage("今天的在线奖励已经领完啦⊙ω⊙");
					break;
				case '4':
					showmessage("暂时没有豆票可以领取⊙ω⊙");
					break;
				default:
					showmessage("未知错误⊙ω⊙");
			}
			break;
		}
		echo json_encode($data);
	}
	function updateStat($num, $uid, $date){
		DB::query("UPDATE x_tdou_log SET num=num+{$num} WHERE uid='{$uid}' AND date='{$date}'");
	}
}

?>