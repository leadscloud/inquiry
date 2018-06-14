<?php
/**
 */
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
$_USER = user_current();
// 权限验证
//current_user_can('tools');

$referer = referer(PHP_FILE,false);

$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

system_head('title','屏蔽IP');

include 'header.php';

switch ($method) {
    case 'save':
        current_user_can('ban-ip');

        $banned_ip = isset($_POST['banned_ip'])?$_POST['banned_ip']:array();
        $banned_ip = trim($banned_ip);
        $banned_ip = explode("\n", $banned_ip);
        $banned_ip = array_filter($banned_ip, 'trim');

        C('banned_ip',$banned_ip);
        echo '保存成功。<a href="'.PHP_FILE.'">返回</a>';

        break;
	default:
        $banned_ip = C('banned_ip');
        $banned_ip = explode(",", $banned_ip);
        echo '<div class="wrap tools">';
        echo   '<h2>'.system_head('title').'</h2>';
        echo    '<div class="clear"></div>';
        echo    '<p>在下面的输入框中输入你想屏蔽的IP，每行一个。</p>';
        echo    '<form action="'.PHP_FILE.'?method=save" method="POST">';
        echo        '<textarea rows="20" cols="40" name="banned_ip">';

        echo implode("\n", $banned_ip);

        echo        '</textarea>';
        echo        '<p class="submit"><input type="submit" class="button-secondary" value="保存"></p>';
        echo    '</form>';
        echo '</div>';
		break;
		break;
}
include 'footer.php';