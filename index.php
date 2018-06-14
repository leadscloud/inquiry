<?php
error_reporting(1);
require_once 'defines.php';
require 'includes/lib/function.base.php';
// 检查是否已配置
if (!is_file(ABS_PATH.'/config.php') || !installed()) {
    redirect(ROOT.'install.php');
}
header('Content-Type: text/html; charset=UTF-8');

if(user_current(false)){
	redirect('admin/index.php');		
}else{
	redirect('login.php');
}
?>