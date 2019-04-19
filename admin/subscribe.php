<?php
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
$_USER = user_current();
system_head('title','订阅管理');
include 'header.php';
 		echo '<div class="wrap">';
        echo   '<h2>订阅管理</h2>';
		echo   '<div class="clear"></div>';
		echo	'<p>Chrome扩展推广, 如果你使用我的系统，请安装下面的插件，在搜索引擎结果页可以查看域名都是谁的，详情请点击下面的链接。</p>';
		echo  '<p><a href="https://sbmzhcn.github.io/serp-analyzer/" target="_blank">Chrome扩展:域名所属人</a></p>';
		echo '</div>';
include 'footer.php';
?>