<?php
require_once '../../defines.php';
require_once 'function.base.php';
$_USER = user_current();
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
	case 'delete':
		// 权限检查
	    current_user_can('inquiry-delete'); //更多 inquiry-view inquiry-mark
		$referer = referer(PHP_FILE);
		$delID = isset($_GET['postid'])?$_GET['postid']:null;
		post_delete($delID);
		header("Location: inquiry.php");
		break;
	case 'bulk':
	    $action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	echo 'Did not select any item.' ;
	    }
	    switch ($action) {
	        case 'mark':
	            foreach ($listids as $postid) {
	            	post_mark($postid);
	            }
	            break;
            case 'delete':
				current_user_can('inquiry-delete');
	            foreach ($listids as $postid) {
	            	post_delete($postid);
	            }
				echo 1;
	            break;
            default:
                echo 'Parameter is invalid.';
                break;
	    }
	    break;
}
?>