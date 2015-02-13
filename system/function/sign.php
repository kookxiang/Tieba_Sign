<?php
if(!defined('IN_KKFRAME')) exit();

function _get_tbs($uid){
	static $tbs = array();
	if($tbs[$uid]) return $tbs[$uid];
	$tbs_url = 'http://tieba.baidu.com/dc/common/tbs';
	$ch = curl_init($tbs_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; MB526 Build/JZO54K) AppleWebKit/530.17 (KHTML, like Gecko) FlyFlow/2.4 Version/4.0 Mobile Safari/530.17 baidubrowser/042_1.8.4.2_diordna_458_084/alorotoM_61_2.1.4_625BM/1200a/39668C8F77034455D4DED02169F3F7C7%7C132773740707453/1','Referer: http://tieba.baidu.com/'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
	$tbs_json = curl_exec($ch);
	curl_close($ch);
	$tbs = json_decode($tbs_json, 1);
	return $tbs[$uid] = $tbs['tbs'];
}

function _verify_cookie($cookie){
	$tbs_url = 'http://tieba.baidu.com/dc/common/tbs';
	$ch = curl_init($tbs_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Linux; U; Android 4.1.2; zh-cn; MB526 Build/JZO54K) AppleWebKit/530.17 (KHTML, like Gecko) FlyFlow/2.4 Version/4.0 Mobile Safari/530.17 baidubrowser/042_1.8.4.2_diordna_458_084/alorotoM_61_2.1.4_625BM/1200a/39668C8F77034455D4DED02169F3F7C7%7C132773740707453/1','Referer: http://tieba.baidu.com/'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	$tbs_json = curl_exec($ch);
	curl_close($ch);
	$tbs = json_decode($tbs_json, 1);
	return $tbs['is_login'];
}

function _get_baidu_userinfo($uid){
	$cookie = get_cookie($uid);
	if(!$cookie) return array('no' => 4);
	$tbs_url = 'http://tieba.baidu.com/f/user/json_userinfo';
	$ch = curl_init($tbs_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Referer: http://tieba.baidu.com/'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	$tbs_json = curl_exec($ch);
	curl_close($ch);
	$tbs_json = mb_convert_encoding($tbs_json, "utf8", "gbk");
	return json_decode($tbs_json, true);
}

function _get_liked_tieba($cookie){
	$pn = 0;
	$kw_name = array();
	$retry = 0;
	while (true){
		$pn++;
		$mylikeurl = "http://tieba.baidu.com/f/like/mylike?&pn=$pn";
		$result = kk_fetch_url($mylikeurl, 0, '', $cookie);
		$result = wrap_text($result);
		$pre_reg = '/<tr><td>.*?<ahref="\/f\?kw=.*?"title="(.*?)"/';
		preg_match_all($pre_reg, $result, $matches);
		$count = 0;
		foreach ($matches[1] as $key => $value) {
			$uname = urlencode($value);
			$_uname = preg_quote($value);
			preg_match('/balvid="([0-9]+)"/i', $result, $fid);
			$kw_name[] = array(
				'name' => mb_convert_encoding($value, 'utf-8', 'gbk'),
				'uname' => $uname,
				'fid' => $fid[1],
			);
			$count++;
		}
		if ($count==0) {
			if ($retry >= 2) break;
			$retry++;
			$pn--;
			continue;
		}
		$retry = 0;
	}
	return $kw_name;
}

function _update_liked_tieba($uid, $ignore_error = false, $allow_deletion = true){
	$date = date('Ymd', TIMESTAMP + 900);
	$cookie = get_cookie($uid);
	if(!$cookie){
		if($ignore_error) return;
		showmessage('请先填写 Cookie 信息再更新', './#baidu_bind');
	}
	$liked_tieba = get_liked_tieba($cookie);
	$insert = $deleted = 0;
	if(!$liked_tieba){
		if($ignore_error) return;
		showmessage('无法获取喜欢的贴吧，请更新 Cookie 信息', './#baidu_bind');
	}
	if($limit = getSetting('max_tieba')){
		$count = count($liked_tieba);
		if($limit < $count){
			if($ignore_error) return;
			showmessage("<p>您共计关注了 {$count} 个贴吧，</p><p>管理员限制了每位用户最多关注 {$limit} 个贴吧</p>", './#liked_tieba');
		}
	}
	$my_tieba = array();
	$query = DB::query("SELECT name, fid, tid FROM my_tieba WHERE uid='{$uid}'");
	while($r = DB::fetch($query)) {
		$my_tieba[$r['name']] = $r;
	}
	foreach($liked_tieba as $tieba){
		if($my_tieba[$tieba['name']]){
			unset($my_tieba[$tieba['name']]);
			if(!$my_tieba[$tieba['name']]['fid']) DB::update('my_tieba', array(
				'fid' => $tieba['fid'],
				), array(
					'uid' => $uid,
					'name' => $tieba['name'],
				), true);
			continue;
		}else{
			DB::insert('my_tieba', array(
				'uid' => $uid,
				'fid' => $tieba['fid'],
				'name' => $tieba['name'],
				'unicode_name' => $tieba['uname'],
				), false, true, true);
			$insert++;
		}
	}
	DB::query("INSERT IGNORE INTO sign_log (tid, uid, `date`) SELECT tid, uid, '{$date}' FROM my_tieba");
	if($my_tieba && $allow_deletion){
		$tieba_ids = array();
		foreach($my_tieba as $tieba){
			$tieba_ids[] = $tieba['tid'];
		}
		$str = "'".implode("', '", $tieba_ids)."'";
		$deleted = count($my_tieba);
		DB::query("DELETE FROM my_tieba WHERE uid='{$uid}' AND tid IN ({$str})");
		DB::query("DELETE FROM sign_log WHERE uid='{$uid}' AND tid IN ({$str})");
	}
	return array($insert, $deleted);
}

function _client_sign($uid, $tieba){
	$cookie = get_cookie($uid);
	preg_match('/BDUSS=([^ ;]+);/i', $cookie, $matches);
	$BDUSS = trim($matches[1]);
	if(!$BDUSS) return array(-1, '找不到 BDUSS Cookie', 0);
	$ch = curl_init('http://c.tieba.baidu.com/c/c/forum/sign');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'User-Agent: Mozilla/5.0 (SymbianOS/9.3; Series60/3.2 NokiaE72-1/021.021; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.1.16352'));
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	$array = array(
		'BDUSS' => $BDUSS,
		'_client_id' => '03-00-DA-59-05-00-72-96-06-00-01-00-04-00-4C-43-01-00-34-F4-02-00-BC-25-09-00-4E-36',
		'_client_type' => '4',
		'_client_version' => '1.2.1.17',
		'_phone_imei' => '540b43b59d21b7a4824e1fd31b08e9a6',
		'fid' => $tieba['fid'],
		'kw' => urldecode($tieba['unicode_name']),
		'net_type' => '3',
		'tbs' => get_tbs($uid),
	);
	$sign_str = '';
	foreach($array as $k=>$v) $sign_str .= $k.'='.$v;
	$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
	$array['sign'] = $sign;
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
	$sign_json = curl_exec($ch);
	curl_close($ch);
	$res = @json_decode($sign_json, true);
	if(!$res) return array(1, 'JSON 解析错误', 0);
	if($res['user_info']){
		$exp = $res['user_info']['sign_bonus_point'];
		return array(2, "签到成功，经验值上升 {$exp}", $exp);
	}else{
		switch($res['error_code']){
			case '340010':		// 已经签过
			case '160002':
			case '3':
				return array(2, $res['error_msg'], 0);
			case '1':			// 未登录
				return array(-1, "ERROR-{$res[error_code]}: ".$res['error_msg'].' （Cookie 过期或不正确）', 0);
			case '160004':		// 不支持
				return array(-1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
			case '160003':		// 零点 稍后再试
			case '160008':		// 太快了
				return array(1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
			default:
				return array(1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
		}
	}
}

function _client_sign_old($uid, $tieba){
	$cookie = get_cookie($uid);
	preg_match('/BDUSS=([^ ;]+);/i', $cookie, $matches);
	$BDUSS = trim($matches[1]);
	if(!$BDUSS) return array(-1, '找不到 BDUSS Cookie', 0);
	$ch = curl_init('http://c.tieba.baidu.com/c/c/forum/sign');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'User-Agent: BaiduTieba for Android 5.1.3', 'client_user_token: '.random(6, true)));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	$array = array(
		'BDUSS' => $BDUSS,
		'_client_id' => 'wappc_138'.random(10, true).'_'.random(3, true),
		'_client_type' => '2',
		'_client_version' => '5.1.3',
		'_phone_imei' => md5(random(16, true)),
		'cuid' => strtoupper(md5(random(16))).'|'.random(15, true),
		'fid' => $tieba['fid'],
		'from' => 'tieba',
		'kw' => urldecode($tieba['unicode_name']),
		'model' => 'Aries',
		'net_type' => '3',
		'stErrorNums' => '0',
		'stMethod' => '1',
		'stMode' => '1',
		'stSize' => random(5, true),
		'stTime' => random(4, true),
		'stTimesNum' => '0',
		'tbs' => get_tbs($uid),
		'timestamp' => time().rand(1000, 9999),
	);
	$sign_str = '';
	foreach($array as $k=>$v) $sign_str .= $k.'='.$v;
	$sign = strtoupper(md5($sign_str.'tiebaclient!!!'));
	$array['sign'] = $sign;
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array));
	$sign_json = curl_exec($ch);
	curl_close($ch);
	$res = @json_decode($sign_json, true);
	if(!$res) return array(1, 'JSON 解析错误', 0);
	if($res['user_info']){
		$exp = $res['user_info']['sign_bonus_point'];
		return array(2, "签到成功，经验值上升 {$exp}", $exp);
	}else{
		switch($res['error_code']){
			case '340010':		// 已经签过
			case '160002':
			case '3':
				return array(2, $res['error_msg'], 0);
			case '1':			// 未登录
				return array(-1, "ERROR-{$res[error_code]}: ".$res['error_msg'].' （Cookie 过期或不正确）', 0);
			case '160004':		// 不支持
				return array(-1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
			case '160003':		// 零点 稍后再试
			case '160008':		// 太快了
				return array(1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
			default:
				return array(1, "ERROR-{$res[error_code]}: ".$res['error_msg'], 0);
		}
	}
}

function _zhidao_sign($uid){
	$ch = curl_init('http://zhidao.baidu.com/submit/user');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'cm=100509&t='.TIMESTAMP);
	$result = curl_exec($ch);
	curl_close($ch);
	return @json_decode($result);
}

function _wenku_sign($uid){
	$ch = curl_init('http://wenku.baidu.com/task/submit/signin');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.43 BIDUBrowser/2.x Safari/537.31'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, get_cookie($uid));
	$result = curl_exec($ch);
	curl_close($ch);
	return @json_decode($result);
}
