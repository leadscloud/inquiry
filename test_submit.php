<?php
require_once 'defines.php';
require_once BLOG_ROOT.'/includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

$title = "sample title";
$username = "Ray";
$useremail = "sbmzhcn@gmail.com";
$userinquiry = "machine";
$userphone = "12345678";
$usercountry = "China";
$useraddress = "Shanghai Pudong xinqu";
$fromcompany = "company";

$metadata = array(
    'materials'	=>	"limestone",
    'application'	=>	"mining",
    'capacity'	=>	"200TPH",
    'products'	=>	"Gold Mining",
    'useplace'	=>	"South Africa",
    'imtype'	=>	"facebook",
    'imvalue'	=>	"im-account",
    'company' => "sample company",
    'timezone_offset' => "8"
);

function inquiry_add2($title,$name,$email,$content,$phone,$country,$address,$fromcompany,$metadata=null) {
    $db = get_conn();
    $ua = getBrowser();

    // print_r($db);
    // print_r($ua);

	$data = array('blog' => 'http://feedback.love4026.org',
              'user_ip' => getip(),
              'user_agent' => $ua['userAgent'],
              'referrer' => referer(),
              'comment_author' => $name,
              'comment_author_email' => $email,
              'comment_author_url' => '',
              'comment_content' => $content);
    
    $timezone_offset = isset($metadata['timezone_offset'])?$metadata['timezone_offset']:null;

    // $testid = $db->insert('wp_inquiry', array(
    //     'title' => "test",
    //     'name' => "test",
    //     'content' => "this is content test. crusher"
    // ));
    
    // echo "testid:".$testid;

    $data = array(
        'title' => $title,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'content' => $content,
        'time' => date('Y-m-d H:i:s',time()),
        'website' => referer() ? referer() : "http://www.google.com/",
        'ip' => getip(),
        'country' => $country,
        'address' => $address,
        'type'	=> akismet_comment_check(Akismet_API_Key,$data)?'trashed':'inquiry',
        'from_company'	=> $fromcompany,
 
        'browser_name' => $ua['name'],
        'browser_version' => $ua['version'],
        'browser_platform' => $ua['platform'],
        'user_agent' => $ua['userAgent'],
        'lang' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        'timezone_offset' => $timezone_offset
        
 
    );

    // print_r($data);


    $msgid = $db->insert('wp_inquiry', $data);

    echo "msgid".$msgid;
        
	return inquiry_edit_meta($msgid,$metadata);
}

$result = inquiry_add2($title,$username,$useremail,$userinquiry,$userphone,$usercountry,$useraddress,$fromcompany,array_map("process_array",$metadata));

print_r($result);

?>
