<?php
// Fix for php without web server
@chdir(dirname(__FILE__));
require_once './system/common.inc.php';
define('SIGN_LOOP', true);
// Do nothing