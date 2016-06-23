<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

$query = DB::query("SELECT uid, cookie FROM member_setting");
while ($result = DB::fetch($query)) {
    if (strpos($result['cookie'], 'BDUSS=') !== false) continue;
    // Decrypt and save cookie
    $cookie = strrev(str_rot13(pack('H*', $result['cookie'])));
    save_cookie($result['uid'], $cookie);
}

saveSetting('version', '1.16.6.23');
showmessage('成功更新到 1.16.6.23！', './');
