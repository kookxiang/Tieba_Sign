<?php
if(!defined('IN_KKFRAME')) exit('Access Denied!');
class plugin_stat extends Plugin{
	var $description = '用于在页面尾部嵌入自定义的统计代码';
	var $modules = array();
	var $version = '1.0';
	function page_footer_js(){
		$data = $this->getSetting('code');
		if($data) return '<div class="hidden">'.$data.'</div>';
	}
	function on_config(){
		if($_POST['code']){
			$this->saveSetting('code', $_POST['code']);
			showmessage('设置已经保存！');
		}else{
			return '<p>页脚统计代码：</p><p><textarea name="code">'.htmlspecialchars($this->getSetting('code')).'</textarea></p>';
		}
	}
}