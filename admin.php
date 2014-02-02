<?php
define ('IN_ADMINCP', true);
define ('DISABLE_CRON', true);
define ('DISABLE_PLUGIN', true);
require_once './system/common.inc.php';
if (! is_admin ($uid))
     exit ();
$formhash = substr (md5 (substr (TIMESTAMP, 0, - 7) . $username . $uid . SYS_KEY . ROOT . 'ADMINCP_ONLY'), 5, 14);

switch ($_GET ['action']){
 case 'load_userstat' :
     $data = array ();
     $date = date ('Ymd');
     $query = DB :: query ('SELECT uid, username FROM member ORDER BY uid');
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] = $result;
         $data [$_uid] ['succeed'] = 0;
         $data [$_uid] ['skiped'] = 0;
         $data [$_uid] ['waiting'] = 0;
         $data [$_uid] ['retry'] = 0;
         $data [$_uid] ['unsupport'] = 0;
         }
     $query = DB :: query ("SELECT uid, COUNT(*) FROM `sign_log` WHERE date='{$date}' AND status='2' GROUP BY uid");
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] ['succeed'] = $result ['COUNT(*)'];
         }
     $query = DB :: query ("SELECT uid, COUNT(*) FROM `sign_log` WHERE date='{$date}' AND status='0' GROUP BY uid");
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] ['waiting'] = $result ['COUNT(*)'];
         }
     $query = DB :: query ("SELECT uid, COUNT(*) FROM `sign_log` WHERE date='{$date}' AND status='1' GROUP BY uid");
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] ['retry'] = $result ['COUNT(*)'];
         }
     $query = DB :: query ("SELECT uid, COUNT(*) FROM `sign_log` WHERE date='{$date}' AND status='-1' GROUP BY uid");
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] ['unsupport'] = $result ['COUNT(*)'];
         }
     $query = DB :: query ("SELECT uid, COUNT(*) FROM `sign_log` WHERE date='{$date}' AND status='-2' GROUP BY uid");
     while ($result = DB :: fetch ($query)){
         $_uid = $result ['uid'];
         $data [$_uid] ['skiped'] = $result ['COUNT(*)'];
         }
     exit (json_encode ($data));
     case 'load_user' :
     $data = array ();
     $query = DB :: query ('SELECT uid, username, email FROM member ORDER BY uid');
     while ($result = DB :: fetch ($query)){
         $data [] = $result;
         }
     exit (json_encode ($data));
     break;
 case 'load_setting' :
     $data = CACHE :: get ('setting');
     unset ($data ['SYS_KEY']);
     exit (json_encode ($data));
     break;
 case 'save_setting' :
     if ($formhash != $_POST ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#setting');
     saveSetting ('account_switch', ($_POST ['account_switch'] ? 1 : 0));
     saveSetting ('register_limit', ($_POST ['register_limit'] ? 1 : 0));
     saveSetting ('register_check', ($_POST ['register_check'] ? 1 : 0));
     saveSetting ('autoupdate', ($_POST ['autoupdate'] ? 1 : 0));
     saveSetting ('block_register', ($_POST ['block_register'] ? 1 : 0));
     saveSetting ('invite_code', stripslashes (daddslashes ($_POST ['invite_code'])));
     saveSetting ('beian_no', stripslashes (daddslashes (htmlspecialchars ($_POST ['beian_no']))));
     saveSetting ('stat_code', stripslashes (daddslashes ($_POST ['stat_code'])));
     saveSetting ('jquery_mode', intval ($_POST ['jquery_mode']));
     saveSetting ('max_tieba', intval ($_POST ['max_tieba']));
     showmessage ('设置已经保存☆Kira~', 'admin.php#setting', 2);
     break;
 case 'deluser' :
     $_uid = intval ($_GET ['uid']);
     if ($uid == $_uid)
         showmessage ('删你自己的号是要作死啊？！', 'admin.php#user');
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#user');
     delete_user ($_uid);
     showmessage ('删除用户成功', 'admin.php#user', 1);
     break;
 case 'update_liked_tieba' :
     $_uid = intval ($_GET ['uid']);
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#user');
     update_liked_tieba ($_uid);
     list ($insert, $deleted) = update_liked_tieba ($_uid);
     showmessage ("喜欢的贴吧列表已经更新,<br>新增{$insert}个贴吧, 删除{$deleted}个贴吧", 'admin.php#user', 1);
     break;
 case 'reset_failure' :
     $_uid = intval ($_GET ['uid']);
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#stat');
     $date = date ('Ymd');
     DB :: query ("UPDATE sign_log SET status='0', retry='0' WHERE uid='{$_uid}' AND date='{$date}' AND status<0");
     showmessage ('已经重置，稍后系统将自动重试', 'admin.php#stat', 1);
     break;
 case 'mail_setting' :
     if ($formhash != $_POST ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#setting');
     $classes = getClasses ();
     $class = $_POST ['mail_sender'];
     if (! $classes [$class])
         showmessage ('选择的邮件发送方式不正确.', 'admin.php#setting');
     if (! $classes [$class] -> isAvailable ())
         showmessage ('选择的邮件发送方式不可用.', 'admin.php#setting');
     saveSetting ('mail_class', $class);
     showmessage ('保存成功<br>(请确认高级设置配置有效)', 'admin.php#setting');
     break;
 case 'mail_advanced' :
     $classes = getClasses ();
     $class = getSetting ('mail_class');
     $obj = $classes [$class];
     if (! $obj)
         showmessage ('选择的邮件发送方式不正确.', 'admin.php#setting');
     if (! $obj -> isAvailable ())
         showmessage ('选择的邮件发送方式不可用.', 'admin.php#setting');
     $_config = $obj -> config;
     if ($_POST ['formhash'] == $formhash){
         foreach ($_config as $k => $v){
             $key = $v [1];
             $value = daddslashes ($_POST [$key]);
             saveSetting ("_mail_{$class}_{$key}", $value);
             }
         CACHE :: save ("mail_{$class}", '');
         showmessage ('保存成功！', 'admin.php#setting');
         }
     $out = array ();
     $setting = array ();
     $query = DB :: query ("SELECT * FROM setting WHERE k LIKE '_mail_{$class}_%'");
     while ($result = DB :: fetch ($query)){
         $key = str_replace ("_mail_{$class}_", '', $result ['k']);
         $setting [$key] = $result ['v'];
         }
     foreach ($_config as $k => $v){
         $key = $v [1];
         $item = array (
            'key' => $v [1],
             'name' => $v [0],
             'description' => $v [2],
             'value' => isset ($setting [$key]) ? $setting [$key] : $v [3],
             'type' => $v [4] ? $v [4] : 'text'
            );
         $out [] = $item;
         }
     echo json_encode ($out);
     break;
 case 'install_plugin' :
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#plugin');
     $plugin_id = $_GET ['pluginid'];
     if (preg_match ('/[^A-Za-z0-9_-.]/', $plugin_id))
         showmessage ('插件ID不合法，请与插件作者联系', 'admin.php#plugin');
     $classfile = ROOT . './plugins/' . $plugin_id . '/plugin.class.php';
     if (! file_exists ($classfile))
         showmessage ('插件文件缺失，请与插件作者联系', 'admin.php#plugin');
     require_once $classfile;
     $classname = "plugin_{$plugin_id}";
     if (! class_exists ("plugin_{$plugin_id}", false))
         showmessage ('插件类不合规范，请与插件作者联系', 'admin.php#plugin');
     $obj = new $classname ();
     $version = 0;
     if (property_exists ($obj, 'version'))
         $version = $obj -> version;
     DB :: insert ('plugin', array (
            'name' => $plugin_id,
             'version' => $version,
             'enable' => 0
            ));
     CACHE :: update ('plugins');
     if (method_exists ($obj, 'on_install'))
         $obj -> on_install ();
     showmessage ('安装插件成功！', 'admin.php#plugin#');
     case 'uninstall_plugin' :
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#plugin');
     $plugin_id = $_GET ['pluginid'];
     if (preg_match ('/[^A-Za-z0-9_-.]/', $plugin_id))
         showmessage ('插件ID不合法，请与插件作者联系', 'admin.php#plugin');
     DB :: query ("DELETE FROM `plugin` WHERE name='{$plugin_id}'");
     $classfile = ROOT . './plugins/' . $plugin_id . '/plugin.class.php';
     if (file_exists ($classfile)){
         require_once $classfile;
         $classname = "plugin_{$plugin_id}";
         if (class_exists ("plugin_{$plugin_id}", false)){
             $obj = new $classname ();
            /**
             * Modified by Jerry Locke starts
             */
             if (property_exists ($obj, 'modules')){ // 删除cron任务
                 foreach ($obj -> modules as $module){
                     if ($module ['type'] == 'cron'){
                         DB :: query ("DELETE FROM `cron` WHERE `id`='" . $module ['cron'] ['id'] . "'");
                         }
                     }
                 }
            /**
             * Modified by Jerry Locke end
             */
             if (method_exists ($obj, 'on_uninstall'))
                 $obj -> on_uninstall ();
             }
         }
     CACHE :: update ('plugins');
     showmessage ('卸载插件成功！', 'admin.php#plugin#');
     case 'enable_plugin' :
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#plugin');
     $plugin_id = $_GET ['pluginid'];
     if (preg_match ('/[^A-Za-z0-9_-.]/', $plugin_id))
         showmessage ('插件ID不合法，请与插件作者联系', 'admin.php#plugin');
     DB :: query ("UPDATE `plugin` SET `enable`=1 WHERE name='{$plugin_id}'");
    /**
     * Modified by Jerry Locke starts
     */
     $classname = "plugin_{$plugin_id}";
     $obj = new $classname ();
     if (property_exists ($obj, 'modules')){ // 添加cron任务
         foreach ($obj -> modules as $module){
             if ($module ['type'] == 'cron'){
                 DB :: insert ('cron', array_merge ($module ['cron'], array (
                            'enabled' => 1
                            )), false, true);
                 }
             }
         }
    /**
     * Modified by Jerry Locke end
     */
     CACHE :: update ('plugins');
     showmessage ('启用插件成功！', 'admin.php#plugin#');
     case 'update_check' :
     $ret = Updater :: check ();
     if (is_array ($ret)){
         $return = array (
            'status' => 1,
             'files' => $ret
            );
         }else{
         $return = array (
            'status' => $ret,
             'files' => array ()
            );
         }
     echo json_encode ($return);
     exit ();
     case 'get_file' :
     echo json_encode (Updater :: loop ());
     exit ();
     case 'write_file' :
     echo json_encode (Updater :: write_file ());
     exit ();
     case 'disable_plugin' :
     if ($formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#plugin');
     $plugin_id = $_GET ['pluginid'];
     if (preg_match ('/[^A-Za-z0-9_-.]/', $plugin_id))
         showmessage ('插件ID不合法，请与插件作者联系', 'admin.php#plugin');
     DB :: query ("UPDATE `plugin` SET `enable`=0 WHERE name='{$plugin_id}'");
    /**
     * Modified by Jerry Locke starts
     */
     $classname = "plugin_{$plugin_id}";
     $obj = new $classname ();
     if (property_exists ($obj, 'modules')){ // 添加cron任务
         foreach ($obj -> modules as $module){
             if ($module ['type'] == 'cron'){
                 DB :: query ("DELETE FROM `cron` WHERE `id`='" . $module ['cron'] ['id'] . "'");
                 }
             }
         }
    /**
     * Modified by Jerry Locke end
     */
     CACHE :: update ('plugins');
     showmessage ('禁用插件成功！', 'admin.php#plugin#');
     case 'config_plugin' :
     $plugin_id = $_REQUEST ['pluginid'];
     if ($_POST ['submit'] && $formhash != $_GET ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#plugin');
     if (preg_match ('/[^A-Za-z0-9_-.]/', $plugin_id))
         showmessage ('插件ID不合法，请与插件作者联系', 'admin.php#plugin');
     $classfile = ROOT . './plugins/' . $plugin_id . '/plugin.class.php';
     if (! file_exists ($classfile))
         showmessage ('插件文件缺失，请与插件作者联系', 'admin.php#plugin');
     require_once $classfile;
     $classname = "plugin_{$plugin_id}";
     if (! class_exists ("plugin_{$plugin_id}", false))
         showmessage ('插件类不合规范，请与插件作者联系', 'admin.php#plugin');
     $obj = new $classname ();
     if (method_exists ($obj, 'on_config')){
         echo json_encode (array (
                'html' => $obj -> on_config ()
                ));
         }else{
         echo json_encode (array (
                'html' => '错误：该插件没有高级配置面板！'
                ));
         }
     break;
 case 'mail_test' :
     $to = DB :: result_first ("SELECT email FROM member WHERE uid='{$uid}'");
     $subject = '[贴吧签到助手] 邮件单发测试';
     $content = "<p>此封邮件仅用于检测邮件系统是否正常工作。</p><p>此封邮件是由邮件系统直接发送的</p>";
     $result = send_mail ($to, $subject, $content, false);
     $subject = '[贴吧签到助手] 邮件群发测试';
     $content = "<p>此封邮件仅用于检测邮件队列是否正常工作。</p><p>此封邮件是从系统邮件队列中读取并发送的</p>";
     send_mail ($to, $subject, $content);
     showmessage (($result ? '2 封邮件已经发送，请查收' : '邮件发送失败'), 'admin.php#setting', 2);
     break;
 case 'send_mail' :
     if ($formhash != $_POST ['formhash'])
         showmessage ('来源不可信，请重试', 'admin.php#mail');
     $title = daddslashes ($_POST ['title']);
     $content = daddslashes ($_POST ['content']);
     $content = nl2br (htmlspecialchars ($content));
     $content .= "<p style=\"padding: 1.5em 1em 0; color: #999; font-size: 12px;\">—— 本邮件由 贴吧签到助手 (<a href=\"{$siteurl}\">{$siteurl}</a>) 管理员发送</p>";
     $query = DB :: query ("SELECT email FROM member");
     while ($result = DB :: fetch ($query)){
         send_mail ($result ['email'], $title, $content);
         }
     showmessage ('已经添加至邮件队列，稍后将由系统自动发送', 'admin.php#mail');
     break;
 case 'load_plugin' :
     exit (json_encode (getPlugins ()));
     break;
 default :
     $classes = getClasses ();
     include template ('admin');
     break;
    }
function getClasses(){
 $handle = opendir (SYSTEM_ROOT . './class/mail/');
 $classes = array ();
 while (1){
 $file = readdir ($handle);
 if (! $file)
     break;
 if (strexists ($file, '.php')){
 $classname = str_replace ('.php', '', $file);
 require_once SYSTEM_ROOT . "./class/mail/{$classname}.php";
 $obj = new $classname ();
 $classes [$obj -> id] = $obj;
 }
 }
 return $classes;
}
function getPlugins(){
 $handle = opendir (ROOT . './plugins/');
 $plugins = $new_plugins = $installed = array ();
 $query = DB :: query ('SELECT name FROM plugin');
 while ($row = DB :: fetch ($query))
 $installed [] = $row ['name'];
 while (1){
 $folder = readdir ($handle);
 if (! $folder)
 break;
 if ($folder == '.' || $folder == '..')
 continue;
 $classfile = ROOT . './plugins/' . $folder . '/plugin.class.php';
 if (! file_exists ($classfile))
 continue;
 require_once $classfile;
 $classname = "plugin_{$folder}";
 if (! class_exists ("plugin_{$folder}", false))
 continue;
 $obj = new $classname ();
 $arr = array (
'id' => $folder,
 'description' => $obj -> description,
 'config' => method_exists ($obj, 'on_config'),
 'enabled' => is_plugin_enabled ($folder),
 'version' => getPluginVersion ($folder),
 'installed' => in_array ($folder, $installed)
);
 if ($arr ['installed']){
 $plugins [] = $arr;
 }else{
 $new_plugins [] = $arr;
 }
 }
 return array_merge ($plugins, $new_plugins);
}
function is_plugin_enabled($pluginid){
 static $enabled_plugin;
 if (! isset ($enabled_plugin)){
 $enabled_plugin = array ();
 $arr = CACHE :: get ('plugins');
 foreach ($arr as $plugin){
 $enabled_plugin [] = $plugin ['id'];
 }
 }
 return in_array ($pluginid, $enabled_plugin);
}
function getPluginVersion($pluginid){
 static $plugin_version;
 if (! isset ($plugin_version)){
 $plugin_version = array ();
 $query = DB :: query ("SELECT name, version FROM `plugin`");
 while ($result = DB :: fetch ($query)){
 $plugin_version [$result ['name']] = $result ['version'];
 }
 }
 return $plugin_version [$pluginid] ? $plugin_version [$pluginid] : 0;
}
