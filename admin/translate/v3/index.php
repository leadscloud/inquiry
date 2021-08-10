<?php
require_once dirname(__FILE__).'/../../../config.php';
ini_get('allow_url_fopen');
// NOTE: Be sure to uncomment the following line in your php.ini file.
// ;extension=php_openssl.dll

// **********************************************
// *** Update or verify the following values. ***
// **********************************************

// Replace the subscriptionKey string value with your valid subscription key.
//https://docs.microsoft.com/en-us/azure/cognitive-services/translator/quickstarts/php
//https://docs.microsoft.com/en-us/azure/cognitive-services/translator/languages

$key = BING_TRANSLATE_KEY;

$host = "https://api.cognitive.microsofttranslator.com";
$path = "/translate?api-version=3.0";

$target = isset($_REQUEST['to']) ? $_REQUEST['to']: "zh-Hans";
$text = $_REQUEST['text'];

$params = '&to=' . $target;

if (!function_exists('com_create_guid')) {
	function com_create_guid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}

function Translate ($host, $path, $key, $params, $text) {

    $headers = "Content-type: application/json\r\n" .
        "Ocp-Apim-Subscription-Key: $key\r\n" .
        "X-ClientTraceId: " . com_create_guid() . "\r\n";
    
    $requestBody = array (
        array (
            'Text' => $text,
        ),
    );
    $content = json_encode($requestBody);

    // NOTE: Use the key 'http' even if you are making an HTTPS request. See:
    // http://php.net/manual/en/function.stream-context-create.php
    $options = array (
        'http' => array (
            'header' => $headers,
            'method' => 'POST',
            'content' => $content,
            // 'ignore_errors' => true
            // 'proxy'           => 'tcp://127.0.0.1:1087',
        	// 'request_fulluri' => true,
        )
    );
    $context  = stream_context_create ($options);

    $result = file_get_contents ($host . $path . $params, false, $context);
    return $result;
}

$result = Translate ($host, $path, $key, $params, $text);
header('Content-Type: application/json');
echo $result;
?>