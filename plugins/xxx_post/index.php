<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
include 'plugins/xxx_post/core.php';
$obj = $_PLUGIN['obj']['xxx_post'];
?>
<style type="text/css">
.small_gray{color:#757575;font-size:12px;}
.small_gray_i{color:#B1B1B1;font-size:12px;font-style:italic;margin:0 0 2em 0;}
.nav-tabs {
  border-bottom: 1px solid #ddd;
  list-style: none;
  padding: 0;
  margin: 0 0 20px 0;
  height:31px
}
.nav-tabs > li {
  margin-bottom: -1px;float: left;  line-height: 20px;
}
.nav-tabs > li > a:hover,
.nav-tabs > li > a:focus {
  border-color: #eeeeee #eeeeee #dddddd;
  cursor:pointer;
}
.nav-tabs > .active > a,
.nav-tabs > .active > a:hover,
.nav-tabs > .active > a:focus {
  color: #555555;
  cursor: default;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-bottom-color: transparent;
}
.nav-tabs > li > a {
  padding: 8px 12px 8px 12px;
  display: block;
  margin-right: 2px;
  line-height: 14px;
  border: 1px solid transparent;
  border-radius: 4px 4px 0 0;
}
.nav-tabs > li > a:hover,
.nav-tabs > li > a:focus {
  border-color: #eeeeee #eeeeee #dddddd;
  text-decoration: none;
  background-color: #eeeeee;
}
.nav-tabs:before,
.nav-tabs:after{
  display: table;
  line-height: 0;
  content: "";
}
.nav-tabs:after{
  clear: both;
}
table.x_table thead tr{background-color:#dedede;}
</style>

<h2>客户端回帖</h2>
<p class="small_gray">当前插件版本：0.3.1 | 更新日期：14-04-29 | Designed By <a href="http://tieba.baidu.com/home/main?un=%D0%C7%CF%D2%D1%A9&fr=index" target="_blank">@星弦雪</a></p>
<p class="small_gray_i"><?php echo '——'.get_random_content();?>
<div>
	<ul class="nav-tabs">
		<li class="active"><a>设置</a></li><li><a>记录</a></li><li><a>帮助</a></li>
	</ul>
</div>

<div class="x_tab_content">

<div>
	<h3>常规</h3>
	<form method="post" id="xxx_post_settings"
		action="plugin.php?id=xxx_post&action=set-settings"
		onsubmit="return post_win(this.action, this.id)">
		<p>
			客户端类型：
		<select name="x_p_client_type" id="x_p_client_type" disabled>
		  <option value="1">iPhone</option>
		  <option value="2">Android</option>
		  <option value="3">Windows Phone</option>
		  <option value="4">Windows 8</option>
		  <option value="5">随机选择一种</option>
		</select>
		</p>
		<p>回帖频率：
		<select name="x_p_frequency" id="x_p_frequency" disabled>
		  <option value="2">每天回一次</option>
		  <option value="1">早晚各回一次</option>
			<?php if ($obj->getSetting ( 'sxbk' ) == 1) echo '<option value="4">极限刷帖</option>'; ?>
		</select>
		，<span id="x_p_runtimes_hide">每次回
		<input type="number" name="x_p_runtimes" id="x_p_runtimes" min="1" max="<?php echo $obj->getSetting('max_runtime', 6); ?>" disabled>
		贴(最多为<?php echo $obj->getSetting('max_runtime', 6); ?>)，</span>发出一贴后等待
		<input type="number" name="x_p_delay" id="x_p_delay" min="0" max="15" disabled>
		分钟再发下一帖</p>
		<p><input type="submit" value="保存设置"></p>
	</form>
	<br>
	<h3>测试</h3>
	<p>随机选取一个帖子，进行一次回帖测试，检查你的设置有没有问题</p>
	<p><a href="plugin.php?id=xxx_post&action=test_post" class="btn"	onclick="return msg_win_action(this.href)">测试回帖</a></p>
<br>
<h3>添加需要回的帖子</h3>
<table class="x_table">
	<thead><tr><td style="width:20px">序号</td><td>贴吧</td><td>贴子</td><td style="width: 20%">操作</td></tr></thead>
	<tbody id="xxx_post_show">
		<tr><td colspan="4"><img src="./template/default/style/loading.gif">载入中请稍后</td></tr>
	</tbody>
</table>
<p>
	<a class="btn" id="xxx_post_add_tid">添加贴子</a>
	<a class="btn" id="x_p_add_tb"	style="margin-left: 5px">添加贴吧</a>
	<a class="btn" id="x_p_del_tid"	style="margin-left: 5px">全部删除</a>
</p>
<br>
<h3>添加回帖内容</h3><p>回帖时随机使用其中之一，不添加的话会使用系统内置的</p>
<table class="x_table">
	<thead><tr><td style="width: 20px">序号</td><td>回帖内容</td><td style="width: 20%">操作</td></tr></thead>
	<tbody id="xxx_post_contents"><tr><td colspan="4"><img src="./template/default/style/loading.gif">载入中请稍后</td></tr></tbody>
</table>
<p>
	<a class="btn" id="xxx_post_add_content">添加内容</a>
	<a class="btn" id="x_p_add_con"	style="margin-left: 5px">批量添加</a>
	<a class="btn" id="x_p_del_con"	style="margin-left: 5px">全部删除</a>
</p>
</div>


<div>
<h2 id="x_p_post_log_tite">当天的回帖记录</h2>
<p>如果帖子已从回帖列表删除，则不会在这里显示</p>
<p id="x_p_pager_text"></p>
<table class="x_table">
	<thead><tr><td style="width: 20px">序号</td><td>贴吧</td><td>贴子</td><td style="width: 20px">成功</td><td style="width: 20px">失败</td></tr></thead>
	<tbody id="x_p_log_tab"><tr><td colspan="5">载入中请稍后</td></tr></tbody>
</table>
</div>

<div>
	<p>使用该插件需做好每日被永封的准备，因发帖插件导致的账号被封、被屏蔽，请使用者自行承担后果</p>
	<h2>关于封禁与解封</h2>
	<p>其实解封很简单的= =（作者表示已经被永封过无数次）</p>
	<p>如果被度受永封的话：</p>
	<p>1.绑定手机秒解</p>
	<p>2.申请人工解封的话，只要你不是丧心病狂地每分钟一贴，一般都可以通过</p>
	<p>如果被吧务封禁的话，只好找吧务承认错误并表示永不再犯= =（不过在官方水楼里刷的话应该吧务不会插手）</p>
</div>
</div>