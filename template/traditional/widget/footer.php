<?php
if(!defined('IN_KKFRAME')) exit();
?>
<p class="copyright"><?php if(getSetting('beian_no')) echo '<a href="http://www.miibeian.gov.cn/" target="_blank" rel="nofollow">'.getSetting('beian_no').'</a> - '; ?><?php HOOK::run('page_footer'); ?></p>
</div>
<script src="<?php echo jquery_path(); ?>"></script>
<script type="text/javascript">var formhash = '<?php echo $formhash; ?>';var version = '<?php echo VERSION; ?>';</script>
<script src="./template/default/js/kk_dropdown.js?version=<?php echo VERSION; ?>"></script>
<?php
if(defined('IN_ADMINCP')){
	echo '<script src="./template/default/js/admin.js?version='.VERSION.'"></script>';
}else{
	echo '<script src="./template/default/js/main.js?version='.VERSION.'"></script>';
}
?>
<script src="./template/traditional/js/fwin.js?version=<?php echo VERSION; ?>"></script>
<?php
HOOK::run('page_footer_js');
if(defined('NEW_VERSION')) echo '<script type="text/javascript">new_version = true</script>';
if(defined('CLOUD_NOT_INITED')) echo '<div class="hidden"><img src="api.php?action=register_cloud" /></div>';
if(defined('CRON_ERROR')) echo '<script type="text/javascript">createWindow().setTitle("警告").setContent("计划任务可能出现故障，请检查配置！").addButton("查看执行情况", function(){ location.href="admin.php#cron"; }).addCloseButton("忽略").append();</script>';
?>