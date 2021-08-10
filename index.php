<?php
/**************************************************************
 * 
 * 外贸留言板系统 
 * 
 * 如果你使用此系统，请保留版本声明。
 * 
 * Copyright (c) Ray
 * Email: <sbmzhcn@gmail.com>
 * Website: https://leadscloud.github.io/
 * 
 ***************************************************************/

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