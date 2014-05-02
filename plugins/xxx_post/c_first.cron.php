<?php
if(!defined('IN_KKFRAME')) exit();
require_once ROOT.'./plugins/xxx_post/core.php';
$date = date('Ymd', TIMESTAMP+900);
$count = DB::result_first("select COUNT(*) from xxx_post_log as a left join xxx_post_setting as b on a.uid=b.uid where a.date='$date' and a.status<b.runtimes and a.retry<b.runtimes*2 and b.frequency<=2");
$first_end=HOOK::getPlugin('xxx_post')->getSetting('first_end');
if(!$first_end) $first_end=15;
$first_end_time=$today+$first_end*3600;
if($first_end_time<TIMPSTAMP) $count=0;
$endtime = TIMESTAMP + 45;
if($count){
	while($endtime > time()){
		$count = DB::result_first("select COUNT(*) from xxx_post_log as a left join xxx_post_setting as b on a.uid=b.uid where a.date='$date' and a.status<b.runtimes and a.retry<b.runtimes*2 and b.frequency<=2 and b.runtime<".TIMESTAMP);
		if($count==0) break;
		$offset = rand(1, $count) - 1;
		$sid = DB::result_first("select sid from xxx_post_log as a left join xxx_post_setting as b on a.uid=b.uid where a.date='$date' and a.status<b.runtimes and a.retry<b.runtimes*2 and b.frequency<=2 and b.runtime<".TIMESTAMP." limit $offset,1");
		if(!$sid) break;
		$tiezi=DB::fetch_first("SELECT * FROM xxx_post_posts WHERE sid='$sid'");
		if(!$tiezi){
			DB::query("UPDATE xxx_post_log SET retry=retry+3 WHERE sid='$sid' AND date='$date'");
			continue;
		}
		$x_content_count = DB::result_first("SELECT COUNT(*) FROM xxx_post_content WHERE uid='{$tiezi[uid]}'");
		$x_content_offset = rand(1, $x_content_count) - 1;
		$x_content = DB::result_first("SELECT content FROM xxx_post_content WHERE uid='{$tiezi[uid]}' limit $x_content_offset,1");
		list($statue,$result) = client_rppost($tiezi['uid'],$tiezi,$x_content);
		if($statue == 2){
			$x_delay=DB::result_first("select delay from xxx_post_setting where uid={$tiezi[uid]}");
			if($x_delay){
				$runtime=TIMESTAMP+$x_delay*56;
				DB::query("UPDATE xxx_post_setting SET runtime=$runtime WHERE uid='{$tiezi[uid]}'");
			}
			DB::query("UPDATE xxx_post_log SET status=status+1 WHERE sid='$sid' AND date='$date'");
		}else if($statue==1||$statue==8){
			$x_runtimes=DB::result_first("select runtimes from xxx_post_setting where uid={$tiezi[uid]}");
			DB::query("UPDATE xxx_post_log SET retry='$x_runtimes' WHERE sid='$sid' AND date='$date'");
		}
		else if($statue==5||$statue==7) continue;
		else DB::query("UPDATE xxx_post_log SET retry=retry+1 WHERE sid='$sid' AND date='$date'");
		if(!defined('SIGN_LOOP')) break;
		sleep(1);
		}
}else{
		cron_set_nextrun($tomorrow + 3600);
}