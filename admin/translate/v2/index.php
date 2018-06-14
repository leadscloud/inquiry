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

$host = "https://api.microsofttranslator.com";
$path = "/V2/Http.svc/Translate";

$target = isset($_REQUEST['to']) ? $_REQUEST['to']: "zh-Hans";
$text = $_REQUEST['text'];

// $text_to_translate = $_REQUEST['text'];
// $to = $_REQUEST['to'];
// $from = $_REQUEST['from'];
// $translator->translate($from, $to, $text_to_translate);
// echo $translator->response->jsonResponse;


$params = '?to=' . $target . '&text=' . urlencode($text);

$content = '';

function Translate ($host, $path, $key, $params, $content) {

    $headers = "Content-type: text/xml\r\n" .
        "Ocp-Apim-Subscription-Key: $key\r\n";

    // NOTE: Use the key 'http' even if you are making an HTTPS request. See:
    // http://php.net/manual/en/function.stream-context-create.php
    $options = array (
        'http' => array (
            'header' => $headers,
            'method' => 'GET'
        )
    );
    $context  = stream_context_create ($options);
    $result = file_get_contents ($host . $path . $params, false, $context);
    return $result;
}

$result = Translate ($host, $path, $key, $params, $content);

//
$translatedStr = "";
$xmlObj = simplexml_load_string($result);
foreach((array)$xmlObj[0] as $val){
    $translatedStr = $val;
}

echo $translatedStr;
?>