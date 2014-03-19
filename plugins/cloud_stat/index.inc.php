<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
$obj = $_PLUGIN['obj']['cloud_stat'];
?>
<h2>签到云统计 *</h2>
<style type="text/css">
.kk_cloud_stat { padding: 30px 20px; }
.kk_cloud_stat p { margin: 10px 0; font-size: 26px; line-height: 42px; font-weight: lighter; text-align: center; }
.kk_cloud_stat span { font-size: 32px; font-family: "Segoe UI Light", "Segoe UI", "幼圆", "Arial"; letter-spacing: 5px; vertical-align: baseline; font-size: 64px; text-shadow: 0 0 15px #777; position: relative; top: 5px; }
.stat_source { position: absolute; bottom: 10px; }
</style>
<div class="kk_cloud_stat">
<p>截止今天, 贴吧签到助手共完成 <span><?php echo intval($obj->getSetting('cloud_tieba')); ?></span> 次签到</p>
<p>为贴吧用户获取 <span><?php echo intval($obj->getSetting('cloud_exp')); ?></span> 点经验.</p>
<p>其中, 当前网站签到 <span><?php echo intval($obj->getSetting('tieba')); ?></span>次, 获取了 <span><?php echo intval($obj->getSetting('exp')); ?></span> 点经验.</p>
</div>
<p class="stat_source">* 数据源自 贴吧签到助手 开放平台 旗下所有签到站点.</p>