<?php
require_once 'defines.php';
//define('BLOG_ROOT', dirname(__FILE__));

// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:OPTIONS, GET, POST'); // 允许option，get，post请求
header('Access-Control-Allow-Headers:*');
// header('Access-Control-Allow-Headers:x-requested-with, content-type'); // 允许x-requested-with请求头

$url_referer = isset($_REQUEST['referer'])?$_REQUEST['referer']:'';
$http_referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';

// if($http_referer=='' && $url_referer=='') {
// 	echo '<a href="https://leadscloud.github.io/serp-analyzer/" target="_blank">Chrome扩展:域名所属人</a>（可以查看搜索引擎结果页，每个域名是哪个公司，精确到人的名字。）<br>';
// 	die('Restricted access!');
// }

require_once BLOG_ROOT.'/includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

function _r($name, $default = NULL) {
	return isset($_POST[$name]) ?
			(is_array($_POST[$name]) ? $default : $_POST[$name]) : $default;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
	$title			=	isset($_POST['title'])?$_POST['title']:null;
	$username		=	isset($_POST['name'])?$_POST['name']:null;
	$useremail		=	isset($_POST['email'])?$_POST['email']:null;
	$userinquiry	=	isset($_POST['content'])?$_POST['content']:null;
	$userphone		=	isset($_POST['phone'])?$_POST['phone']:null;
	$usercountry	=	isset($_POST['country'])?$_POST['country']:null;
	$useraddress	=	isset($_POST['address'])?$_POST['address']:null;
	$fromcompany	=	isset($_POST['from_company'])?$_POST['from_company']:null;
	$company		=	isset($_POST['company'])?$_POST['company']:null;

	$materials		=	isset($_POST['materials'])?$_POST['materials']:null;
	$application	=	isset($_POST['application'])?$_POST['application']:null;
	$capacity		=	isset($_POST['capacity'])?$_POST['capacity']:null;
	$products		=	isset($_POST['products'])?$_POST['products']:null;
	$useplace		=	isset($_POST['useplace'])?$_POST['useplace']:null;

	$imtype			=	isset($_POST['imtype'])?$_POST['imtype']:null;
	$imvalue		=	isset($_POST['imvalue'])?$_POST['imvalue']:null;

	$timezone_offset = isset($_POST['timezone_offset'])?$_POST['timezone_offset']:null;

	
	//额外的字段可以在这儿添加
	$metadata = array(
		'materials'	=>	$materials,
		'application'	=>	$application,
		'capacity'	=>	$capacity,
		'products'	=>	$products,
		'useplace'	=>	$useplace,
		'imtype'	=>	$imtype,
		'imvalue'	=>	$imvalue,
		'company' => $company,
		'timezone_offset' => $timezone_offset
	);
	function process_array($data){
		$result = '';
		if(is_array($data)){
			foreach( $data as $k => $v ) {
				$result.= $v.', ';
			}
			$data = $result;
		}
		return $data;
	}

	check(array(
    // 用户名不能为空
    array('name','VALIDATE_EMPTY','The username field is empty.'),
    // 用户名长度必须是2-30个字符
    array('name','VALIDATE_LENGTH','The username field length must be %d-%d characters.',2,30),
				
  ));
	//如果有邮箱，验证它
	if($useremail!=null)
		check(array(
      array('email','VALIDATE_EMPTY','Please enter an e-mail address.'),
      array('email','IS_EMAIL','You must provide an correct e-mail address.')
    ));
	  // 验证留言内容
    check(array(
      array('content','VALIDATE_EMPTY','Please input your inquiry.'),
			array('content','VALIDATE_HAVE_LINK','The content can not contain links.'),
      array('content','VALIDATE_LENGTH','The content field length must be %d-%d characters.',5,2000)
    ));
	
	$return_info	=	isset($_POST['return_msg'])?$_POST['return_msg']:'Thanks for your submit. We will response as soon as possible!';
	$return_json = array("code" => 1, "message" => $return_info);
	
	if(inquiry_add($title,$username,$useremail,$userinquiry,$userphone,$usercountry,$useraddress,$fromcompany,array_map("process_array",$metadata)))
	{ 
		//echo "..".$_SERVER['HTTP_ACCEPT'];
	  if (is_ajax()) {
        ajax_success($return_json);
      }else if(false !== strpos( $_SERVER['HTTP_ACCEPT'], 'application/json' )){
      	ajax_success($return_json);
      }else{
		alert_echo($return_info,referer(PHP_FILE));
	  }
		//Msg($return_info);
	}
	else
	{
		if (is_ajax() || false !== strpos( $_SERVER['HTTP_ACCEPT'], 'application/json' )) {
			ajax_error(array("code" => 0, "message" => "submit failed"));
		}else{
			alert_echo('submit failed !', referer(PHP_FILE));
		}
	}
} 
else 
{
	if (is_ajax()) {
		ajax_error(array("code" => 0, "message" => "Parameter is invalid."));
    }else{
		Msg('Parameter is invalid.');
		//alert_echo($return_info,referer(PHP_FILE));
	}
}


function alert_ppc_echo($string,$url=null){
	if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    echo '写入你的ppc JS代码';
	
	echo '<script type="text/javascript">alert("'.$string.'");window.location.href="'.$url.'"</script>';
	ob_flush(); 
	exit();
}

?>