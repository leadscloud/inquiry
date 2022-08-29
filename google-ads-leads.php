<?php
/**
 * Google广告的 广告与附加信息中的 潜在客户表单 webhook
 * 如果没有这个自动收集的工具，就需要进入到账号中，每次下载表单数据
 * 此脚本把google ads lead form每次发送的数据保存到留言表中
 */
require_once 'defines.php';
//define('BLOG_ROOT', dirname(__FILE__));
require_once BLOG_ROOT . '/includes/lib/function.base.php';

// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// ini_set('display_startup_errors', TRUE);

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:OPTIONS, GET, POST'); // 允许option，get，post请求
header('Access-Control-Allow-Headers:*');

$form_data = file_get_contents("php://input");
$data = json_decode($form_data, true);

$pass = "your_secret_pass";

// https://developers.google.com/google-ads/webhook/docs/implementation

/**
 *  {
 * "lead_id": "TeSter-123-ABCDEFGHIJKLMNOPQRSTUVWXYZ-abcdefghijklmnopqrstuvwxyz-0123456789-AaBbCcDdEeFfGgHhIiJjKkLl",
 * "user_column_data": [
 *   {
 *     "column_name": "Full Name",
 *     "string_value": "FirstName LastName",
 *     "column_id": "FULL_NAME"
 *   },
 *   {
 *     "column_name": "Company Name",
 *     "string_value": "CompanyName",
 *     "column_id": "COMPANY_NAME"
 *   },
 *   {
 *     "column_name": "Work Email",
 *     "string_value": "work-test@example.com",
 *     "column_id": "WORK_EMAIL"
 *   },
 *   {
 *     "column_name": "Job Title",
 *     "string_value": "JobTitle",
 *     "column_id": "JOB_TITLE"
 *   }
 * ],
 * "api_version": "1.0",
 * "form_id": 52083961646,
 * "campaign_id": 10000000000,
 * "google_key": "123456",
 * "is_test": true,
 * "gcl_id": "TeSter-123-ABCDEFGHIJKLMNOPQRSTUVWXYZ-abcdefghijklmnopqrstuvwxyz-0123456789-AaBbCcDdEeFfGgHhIiJjKkLl",
 * "adgroup_id": 20000000000,
 * "creative_id": 30000000000
 * }
 */


if ($pass == $data["google_key"]) {

    $lead_id = $data['lead_id'];
    $api_version = $data['api_version'];
    $form_id = $data['form_id'];
    $campaign_id = $data['campaign_id'];
    $adgroup_id = $data['adgroup_id'];
    $creative_id = $data['creative_id'];

    $client_name = '';
    $client_phone = '';
    $client_email = '';

    $text = '';

    foreach($data['user_column_data'] as $field) {
        if($field["column_id"] == "FULL_NAME" || $field["column_id"] == "FIRST_NAME"){
			$client_name = $field["string_value"];
		}
        if($field["column_id"] == "PHONE_NUMBER" || $field["column_id"] == "WORK_PHONE"){
			$client_phone = $field["string_value"];
		}
        if($field["column_id"] == "EMAIL" || $field["column_id"] == "WORK_EMAIL"){
			$client_email = $field["string_value"];
		}

        $text .= "<b>" .$field["column_name"] . ":</b> " . $field["string_value"] . "<br>\r\n";

    }

    $text .= "<br><br>\r\n";
    $text .= "form_id: " . $form_id . "<br>\r\n";
    $text .= "campaign_id: " . $campaign_id . "<br>\r\n";
    $text .= "adgroup_id: " . $adgroup_id . "<br>\r\n";
    $text .= "creative_id: " . $creative_id . "<br>\r\n";

    if (inquiry_add($lead_id, $client_name, $client_email, $text, $client_phone, '', '', 'Google-Ads-Leads', null, false)) {
        echo "insert success" . $lead_id;
    } else {
        http_response_code(500);
        echo "insert failed";
    }
}
