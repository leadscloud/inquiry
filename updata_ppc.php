<?php

require_once 'defines.php';

$url_referer = isset($_REQUEST['referer']) ? $_REQUEST['referer'] : '';
$http_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if ($http_referer == '' && $url_referer == '') die('Restricted access!');


require_once BLOG_ROOT . '/includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$title			=	isset($_POST['title']) ? $_POST['title'] : null;
	$username		=	isset($_POST['name']) ? $_POST['name'] : null;
	$useremail		=	isset($_POST['email']) ? $_POST['email'] : null;
	$userinquiry	=	isset($_POST['content']) ? $_POST['content'] : null;
	$userphone		=	isset($_POST['phone']) ? $_POST['phone'] : null;
	$usercountry	=	isset($_POST['country']) ? $_POST['country'] : null;
	$useraddress	=	isset($_POST['address']) ? $_POST['address'] : null;
	$fromcompany	=	isset($_POST['from_company']) ? $_POST['from_company'] : null;

	$materials		=	isset($_POST['materials']) ? $_POST['materials'] : null;
	$application	=	isset($_POST['application']) ? $_POST['application'] : null;
	$capacity		=	isset($_POST['capacity']) ? $_POST['capacity'] : null;
	$products		=	isset($_POST['products']) ? $_POST['products'] : null;
	$useplace		=	isset($_POST['useplace']) ? $_POST['useplace'] : null;
	$imtype		=	isset($_POST['imtype']) ? $_POST['imtype'] : null;
	$imvalue		=	isset($_POST['imvalue']) ? $_POST['imvalue'] : null;
	//额外的字段可以在这儿添加
	$metadata = array(
		'materials'	=>	$materials,
		'application'	=>	$application,
		'capacity'	=>	$capacity,
		'products'	=>	$products,
		'useplace'	=>	$useplace,
		'imtype'	=>	$imtype,
		'imvalue'	=>	$imvalue,
	);

	function process_array($data)
	{
		$result = '';
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$result .= $v . ', ';
			}
			$data = $result;
		}
		return $data;
	}



	check(array(
		// 用户名不能为空
		array('name', 'VALIDATE_EMPTY', 'The username field is empty.'),
		// 用户名长度必须是2-30个字符
		array('name', 'VALIDATE_LENGTH', 'The username field length must be %d-%d characters.', 2, 30),
	));

	//如果有邮箱，验证它
	if ($useremail != null)
		check(array(
			array('email', 'VALIDATE_EMPTY', 'Please enter an e-mail address.'),
			array('email', 'IS_EMAIL', 'You must provide an correct e-mail address.')
		));

	// 验证留言内容
	check(array(
		array('content', 'VALIDATE_EMPTY', 'Please input your inquiry.'),
		array('content', 'VALIDATE_HAVE_LINK', 'The content can not contain links.'),
		array('content', 'VALIDATE_LENGTH', 'The content field length must be %d-%d characters.', 5, 2000)
	));

	$return_info	=	isset($_POST['return_msg']) ? $_POST['return_msg'] : 'Thanks for your submit. We will response as soon as possible!';

	if (inquiry_add($title, $username, $useremail, $userinquiry, $userphone, $usercountry, $useraddress, $fromcompany, array_map("process_array", $metadata))) {
		if (is_ajax()) {
			ajax_success($return_info);
		} else {
			alert_ppc_echo($return_info, referer(PHP_FILE));
		}
		//Msg($return_info);
	} else {
		if (is_ajax()) {
			ajax_error('submit failed');
		} else {
			alert_ppc_echo('submit failed !', referer(PHP_FILE));
		}
	}
} else {
	if (is_ajax()) {
		ajax_error('Parameter is invalid.');
	} else {
		Msg('Parameter is invalid.');
		//alert_ppc_echo($return_info,referer(PHP_FILE));
	}
}

/**
 * 显示JS代码，弹出提示框
 * 可以在此加入PPPC统计代码
 */

function alert_ppc_echo($string, $url = null)
{
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=utf-8');
	}

	echo "<script type=\"text/javascript\">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-476894-8']);
  _gaq.push(['_setDomainName', 'crusher.com']);
  _gaq.push(['_setCustomVar',1, 'Vis-type', 'order-online',2]);
  _gaq.push(['_trackPageview']);
</script>
<script type=\"text/javascript\">
  (function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>"; //写入你的ppc JS代码

	echo '<script type="text/javascript">alert("' . $string . '");window.location.href="' . $url . '"</script>';
	ob_flush();
	exit();
}