<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_xxx_post extends Plugin{
	var $description = '可以模仿客户端进行回帖（三倍经验yoooooooooooooo）';
	var $modules = array (
		array ('id' => 'index',	'type' => 'page','title' => '客户端回帖','file' => 'index.php'),
		array('type' => 'cron', 'cron' => array('id' => 'xxx_post/c_daily', 'order' => '101')),
		array('type' => 'cron', 'cron' => array('id' => 'xxx_post/c_first', 'order' => '103')),
		array('type' => 'cron', 'cron' => array('id' => 'xxx_post/c_se', 'order' => '105')),
		array('type' => 'cron', 'cron' => array('id' => 'xxx_post/c_sxbk', 'order' => '109')),
	);
	var $version='0.3.1';
	function checkCompatibility(){
		if(version_compare(VERSION, '1.14.4.24', '<')) showmessage('签到助手版本过低，请升级');
	}
	function page_footer_js() {
		echo '<script src="plugins/xxx_post/main.js"></script>';
	}
	function install() {
		$query = DB::query ( 'SHOW TABLES' );
		$tables = array ();
		while ($table= DB::fetch($query)) $tables[]=implode ('', $table );
		if (!in_array ( 'xxx_post_posts', $tables )){
		runquery("
			CREATE TABLE IF NOT EXISTS `xxx_post_posts` (
				`sid` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`uid` int(10) unsigned NOT NULL,
				`fid` int(10) unsigned NOT NULL,
				`tid` int(12) unsigned NOT NULL,
				`name` varchar(127) NOT NULL,
				`unicode_name` varchar(512) NOT NULL,
				`post_name` varchar(127) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;

			CREATE TABLE IF NOT EXISTS `xxx_post_setting` (
				`uid` int(10) unsigned NOT NULL PRIMARY KEY,
				`client_type` tinyint(1) NOT NULL DEFAULT '5',
				`frequency` tinyint(1) NOT NULL DEFAULT '2',
				`delay` tinyint(2) NOT NULL DEFAULT '1',
				`runtime` int(10) unsigned NOT NULL DEFAULT '0',
				`runtimes` int(5) unsigned NOT NULL DEFAULT '6'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;

			CREATE TABLE IF NOT EXISTS `xxx_post_content` (
				`cid` int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
				`uid` int(10) unsigned NOT NULL,
				`content` varchar(1024) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;

			CREATE TABLE IF NOT EXISTS `xxx_post_log` (
				`sid` int(10) unsigned NOT NULL,
				`uid` int(10) unsigned NOT NULL,
				`date` int(11) NOT NULL DEFAULT '0',
				`status` tinyint(4) NOT NULL DEFAULT '0',
				`retry` tinyint(3) unsigned NOT NULL DEFAULT '0',
				UNIQUE KEY `sid` (`sid`,`date`),
				KEY `uid` (`uid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
			$this->saveSetting ( 'sxbk', '0' );
			$this->saveSetting ( 'se', '21' );
			$this->saveSetting ( 'first_end','15');
		}
	}
	function uninstall() {
		DB::query ( "DROP TABLE xxx_post_content,xxx_post_log,xxx_post_posts,xxx_post_setting" );
		showmessage ( "数据库删除成功。" );
	}
	function on_upgrade($from_version){
		switch ($from_version){
			case '0':
			case '0.2.2_13':
			case '0.2.3':
			case '0.3.0':
				runquery("
					UPDATE cron SET id='xxx_post/c_daily' WHERE id='xxx_post_daily';
					UPDATE cron SET id='xxx_post/c_first' WHERE id='xxx_post';
					UPDATE cron SET id='xxx_post/c_se' WHERE id='xxx_post_se';
					UPDATE cron SET id='xxx_post/c_sxbk' WHERE id='xxx_post_sxbk';
					");
				$this->saveSetting ( 'sxbk', '0' );
				$this->saveSetting ( 'se', '21' );
				$this->saveSetting ( 'first_end','15');
				return '0.3.1';
			default:
				throw new Exception("Unknown plugin version: {$from_version}");
		}
	}
	function on_config() {
		if ($_POST) {
			$sxbkset=trim($_POST ['sxbkset']);
			$se_set=intval(trim($_POST['se_set']));
			$first_end=intval(trim($_POST['first_end']));
			$max_runtime=intval($_POST['max_runtime']);
			$max_runtime = max(6, $max_runtime);
			if (! $sxbkset)	$sxbkset = 0;
			if($se_set<12) $se_set=12;
			else if ($se_set>22) $se_set=22;
			if($first_end<1) $first_end=1;
			else if ($first_end>22) $first_end=22;
			$this->saveSetting('sxbk',$sxbkset);
			$this->saveSetting('se',$se_set);
			$this->saveSetting('first_end',$first_end);
			$this->saveSetting('max_runtime', $max_runtime);
			showmessage ( "设置保存成功" );
		} else {
			$sxbk=$this->getSetting('sxbk');
			$se_set=$this->getSetting('se');
			$first_end=$this->getSetting('first_end');
			$max_runtime=$this->getSetting('max_runtime', 6);
			$sxbk = $sxbk ? 'checked="cheched"' : '';
			return <<<EOF
<P><label><input type="checkbox" name="sxbkset" value="1" $sxbk> 允许极限刷帖（此功能及其消耗服务器资源，而且会导致sign_retry任务无法执行，如果你是管理员，可以考虑禁用这个选项）</label></p>
<p>时间控制(24小时制):</p>
<p>在<input type="number" name="first_end" min="1" max="22" value="$first_end" style="outline:none;margin-left:4px;margin-right:4px"/>点之前结束第一次回帖</p>
<p>在<input type="number" name="se_set" min="12" max="22" style="outline:none;margin-left:4px;margin-right:4px" value="$se_set"/>点之后开始第二次回帖</p>
<p>每位用户每次最多回<input type="number" name="max_runtime" min="6" max="999" style="outline:none;margin-left:4px;margin-right:4px" value="$max_runtime"/>个帖子</p>
EOF;
		}
	}
	function handleAction(){
		global $uid;
		if(!$uid) return;
		switch ($_GET ['action']) {
			case 'delsid' :
				$_sid = intval ( $_GET ['sid'] );
				DB::query ( "DELETE FROM xxx_post_posts WHERE sid='{$_sid}'" );
				$data ['msg'] = "删除成功";
				break;
			case 'del-all-tid' :
				DB::query ( "DELETE FROM xxx_post_posts WHERE uid='{$uid}'" );
				$data ['msg'] = "删除成功";
				break;
			case 'delcont' :
				$cid = intval ( $_GET ['cid'] );
				DB::query ( "DELETE FROM xxx_post_content WHERE cid='{$cid}'" );
				$data ['msg'] = "删除成功";
				break;
			case 'del-all-cont' :
				DB::query ( "DELETE FROM xxx_post_content WHERE uid='{$uid}'" );
				$data ['msg'] = "删除成功";
				break;
			case 'set-content' :
				$contx = $_POST ['post_content'];
				if (! $contx) {
					$data ['msg'] = "设置失败，请输入字符串";
				} else {
					DB::insert ( 'xxx_post_content', array (
							'uid' => $uid,
							'content' => $contx
					) );
					$data ['msg'] = "设置成功";
				}
				break;
			case 'set-cont-plus' :
				$contplus = $_POST ['x_p_contant'];
				if (! trim ( $contplus )) {
					$data ['msg'] = "设置失败，请输入字符串";
				} else {
					$cp_array = explode ( "\n", trim ( $contplus ) );
					foreach ( $cp_array as $contx ) {
						if (! trim ( $contx ))
							continue;
						DB::insert ( 'xxx_post_content', array (
								'uid' => $uid,
								'content' => $contx
						) );
					}
					$data ['msg'] = "设置成功";
				}
				break;
			case 'set-settings' :
				$client_type = intval($_POST ['x_p_client_type']);
				$frequency = intval($_POST ['x_p_frequency']);
				$runtimes = intval($_POST ['x_p_runtimes']);
				$delay = intval($_POST ['x_p_delay']);
				$max_runtime=$this->getSetting('max_runtime', 6);
				$runtimes = min($max_runtime, $runtimes);
				if ($delay < 0)	$delay = 0;
				else if ($delay > 15)  $delay = 15;
				if ($runtimes < 1)	$delay = 1;
				else if ($runtimes > 6)  $delay = 6;
				DB::query ( "replace into xxx_post_setting (uid,client_type,frequency,delay,runtimes) values($uid,$client_type,$frequency,$delay,$runtimes)" );
				$data ['msg'] = "设置成功";
				break;
			case 'post-settings' :
				$query = DB::query ( "SELECT * FROM xxx_post_posts WHERE uid='$uid'" );
				while ( $result = DB::fetch ( $query ) ) {
					$data ['tiebas'] [] = $result;
				}
				$query = DB::query ( "SELECT * FROM xxx_post_content WHERE uid='$uid'" );
				while ( $result = DB::fetch ( $query ) ) {
					$data ['contents'] [] = $result;
				}
				$data ['count1'] = count ( $data ['tiebas'] );
				$data ['count2'] = count ( $data ['contents'] );
				break;
			case 'post-adv-settings' :
				$query = DB::query ( "SELECT * FROM xxx_post_setting WHERE uid='$uid'" );
				while ( $result = DB::fetch ( $query ) ) {
					$data ['settings'] = $result;
				}
				if (! $data ['settings'] ['client_type']) {
					DB::query ( "insert into xxx_post_setting set uid=$uid");
					$data ['settings'] ['client_type'] = 5;
					$data ['settings'] ['frequency'] = 2;
					$data ['settings'] ['delay'] = 1;
					$data ['settings'] ['runtimes'] = 6;
				}
				break;
			case 'add-tieba' :
				$tieba = $_POST ['xxx_post_add_tieba'];
				$ch = curl_init ('http://tieba.baidu.com/f?kw='.urlencode(iconv("utf-8", "gbk", $tieba)).'&fr=index');
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
				$contents = curl_exec ( $ch );
				curl_close ( $ch );
				$fid = 0;
				preg_match('/"forum_id"\s?:\s?(?<fid>\d+)/', $contents, $fids);
				$fid = $fids ['fid'];
				if ($fid == 0) {
					$data ['msg'] = "添加失败，请检查贴吧名称并重试";
					$data ['msgx'] = 0;
					break;
				}
				preg_match ( '/fname="(.+?)"/', $contents, $fnames );
				$unicode_name = urlencode($fnames [1]);
				$fname = $fnames [1];
				DB::insert ( 'xxx_post_posts', array (
					'uid' => $uid,
					'fid' => $fid,
					'tid' => 0,
					'name' => $fname,
					'unicode_name' => $unicode_name,
					'post_name' =>'随机'
				) );
				$data ['msg'] = "添加成功";
				break;
			case 'get-tid' :
				$tieurl = $_POST ['xxx_post_tid'];
				preg_match ( '/tieba\.baidu\.com\/p\/(?<tid>\d+)/', $tieurl, $tids );
				$tid=$tids ['tid'];
				$ch = curl_init ('http://tieba.baidu.com/p/'.$tid);
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
				$contents = curl_exec ( $ch );
				curl_close ( $ch );
				$fid = 0;
				preg_match ( '/"forum_id"\s?:\s?(?<fid>\d+)/', $contents, $fids );
				$fid =$fids ['fid'];
				if ($fid == 0) {
					$data ['msg'] = "添加失败，请检查帖子地址并重试";
					$data ['msgx'] = 0;
					break;
				}
				preg_match ( '/fname="(.+?)"/', $contents, $fnames );
				$unicode_name = urlencode($fnames [1]);
				$fname = $fnames [1];
				preg_match ( '/title:"(.*?)"/', $contents, $post_names );
				$post_name = $post_names [1];
				DB::insert ( 'xxx_post_posts', array (
						'uid' => $uid,
						'fid' => $fid,
						'tid' => $tid,
						'name' => $fname,
						'unicode_name' => $unicode_name,
						'post_name' => $post_name
				) );
				$data ['msg'] = "添加成功";
				break;
			case 'test_post' :
				include 'plugins/xxx_post/core.php';
				$tiezi_count = DB::result_first ( "SELECT COUNT(*) FROM xxx_post_posts WHERE uid='$uid'" );
				$tiezi_offset = rand(1, $tiezi_count) - 1;
				$tiezi=DB::fetch_first ( "SELECT * FROM xxx_post_posts WHERE uid='$uid' limit $tiezi_offset,1" );
				if (! $tiezi) showmessage ('没有添加帖子，请先添加！');
				$x_content_count = DB::result_first("SELECT COUNT(*) FROM xxx_post_content WHERE uid='$uid'");
				$x_content_offset = rand(1, $x_content_count) - 1;
				$x_content = DB::result_first("SELECT content FROM xxx_post_content WHERE uid='$uid' limit $x_content_offset,1");
				list ( $status, $result ) = client_rppost ( $uid, $tiezi, $x_content);
				$status = $status == 2 ? '发帖成功' : '发帖失败';
				showmessage ( "<p>测试帖子：【{$tiezi[name]}吧】{$tiezi[post_name]}</p><p>测试结果：{$status}</p><p>详细信息：{$result}</p>" );
				break;
			case 'post-log' :
				$date = date ( 'Ymd' );
				$data ['date'] = date ( 'Y-m-d' );
			case 'post-history' :
				if ($_GET ['action'] == 'post-history') {
					$date = intval ( $_GET ['date'] );
					$data ['date'] = substr ( $date, 0, 4 ) . '-' . substr ( $date, 4, 2 ) . '-' . substr ( $date, 6, 2 );
				}
				$data ['log'] = array ();
				$query = DB::query ( "SELECT * FROM xxx_post_log l LEFT JOIN xxx_post_posts t ON t.sid=l.sid WHERE l.uid='$uid' AND l.date='$date'" );
				while ( $result = DB::fetch ( $query ) ) {
					if (! $result ['sid']) continue;
					$data ['log'] [] = $result;
				}
				$data ['count'] = count ( $data ['log'] );
				$data ['before_date'] = DB::result_first ( "SELECT date FROM xxx_post_log WHERE uid='{$uid}' AND date<'{$date}' ORDER BY date DESC LIMIT 0,1" );
				$data ['after_date'] = DB::result_first ( "SELECT date FROM xxx_post_log WHERE uid='{$uid}' AND date>'{$date}' ORDER BY date ASC LIMIT 0,1" );
				break;
		}
		echo json_encode ( $data );
	}
}
