<?php
if(!defined('IN_KKFRAME')) exit();
$date = date('Ymd', TIMESTAMP);
$count = DB::result_first("SELECT COUNT(*) FROM `sign_log` WHERE status IN (0, 1) AND date='{$date}'");
@set_time_limit(60);
$multi_thread = getSetting('channel') == 'dev' && getSetting('multi_thread');
$endtime = $multi_thread ? TIMESTAMP + 10 : TIMESTAMP + 45;
if($nowtime - $today < 1800){
	cron_set_nextrun($today + 1800);
}elseif($count){
	if($multi_thread){
		$ret = MultiThread::registerThread(5, 10);
		if($ret) MultiThread::newCronThread();
	}
	if(getSetting('next_cron') < TIMESTAMP - 3600) cron_set_nextrun(TIMESTAMP - 1);
	while($endtime > time()){
		if($count <= 0) break;
		$offset = getSetting('random_sign') ? rand(1, $count) - 1 : 0;
		$res = DB::fetch_first("SELECT tid, status FROM `sign_log` WHERE status IN (0, 1) AND date='{$date}' ORDER BY uid LIMIT {$offset},1");
		$tid = $res['tid'];
		if(!$tid) break;
		if($res['status'] == 2 || $res['status'] == -2) continue;
		$tieba = DB::fetch_first("SELECT * FROM my_tieba WHERE tid='{$tid}'");
		if($tieba['skiped'] || !$tieba){
			DB::query("UPDATE sign_log set status='-2' WHERE tid='{$tieba[tid]}' AND date='{$date}'");
			continue;
		}
		$uid = $tieba['uid'];
		$setting = get_setting($uid);
		list($status, $result, $exp) = client_sign($uid, $tieba);
		if($status == 2){
			if($exp){
				DB::query("UPDATE sign_log SET status='2', exp='{$exp}' WHERE tid='{$tieba[tid]}' AND date='{$date}'");
				$time = 2;
			}else{
				DB::query("UPDATE sign_log SET status='2' WHERE tid='{$tieba[tid]}' AND date='{$date}' AND status<2");
				$time = 0;
			}
		}else{
			$retry = DB::result_first("SELECT retry FROM sign_log WHERE tid='{$tieba[tid]}' AND date='{$date}' AND status<2");
			if($retry >= 100){
				DB::query("UPDATE sign_log SET status='-1' WHERE tid='{$tieba[tid]}' AND date='{$date}' AND status<2");
			}elseif($status == 1){
				DB::query("UPDATE sign_log SET status='1', retry=retry+10 WHERE tid='{$tieba[tid]}' AND date='{$date}' AND status<2");
			}else{
				DB::query("UPDATE sign_log SET status='1', retry=retry+33 WHERE tid='{$tieba[tid]}' AND date='{$date}' AND status<2");
			}
			$time = 1;
		}
		if($time){
			sleep($time);
			$count--;
		}
	}
	if($multi_thread){
		$ret = MultiThread::registerThread(5, 10);
		if($ret) MultiThread::newCronThread();
	}
}else{
	cron_set_nextrun($nowtime + 1800);
}
