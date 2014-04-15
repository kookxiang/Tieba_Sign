<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h2>我喜欢的贴吧</h2>
<p>如果此处显示的贴吧有缺失，请<a href="index.php?action=refresh_liked_tieba" onclick="return msg_redirect_action(this.href+'&formhash='+formhash)">点此刷新喜欢的贴吧</a>.</p>
<table>
<thead><tr><td style="width: 40px">#</td><td>贴吧</td><td style="width: 65px">忽略签到</td></tr></thead>
<tbody></tbody>
</table>