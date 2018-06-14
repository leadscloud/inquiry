<?php
define('ABS_PATHS',dirname(__FILE__));
include dirname(__FILE__).'/defines.php';
$db_name	=	DB_NAME;
$db_prefix	=	DB_PREFIX;
define('DB_FILE',BLOG_ROOT.'/content/'.DB_NAME);
require_once ABS_PATH.'/includes/lib/function.base.php';


$db = get_conn(); 
//clear error field
$db->delete('#@_user_meta',array('key'=>'password'));
$db->delete('#@_user_meta',array('key'=>'authcode'));
//setting
$username = 'admin';
$password = 'admin';

//start reset password
$authcode = cookie_get('authcode');
$user = user_get_byname($username);
user_edit($user['uid'],array(
    'password' => md5($password.$authcode),
    'authcode' => $authcode,
));