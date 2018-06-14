<?php
/**
 * This file will retuen JSON response
 */
require_once('config.inc.php');
require_once('class/ServicesJSON.class.php');
require_once('class/MicrosoftTranslator.class.php');

$translator = new MicrosoftTranslator(ACCOUNT_KEY);
$text_to_translate = $_REQUEST['text'];
$to = $_REQUEST['to'];
$from = $_REQUEST['from'];
$translator->translate($from, $to, $text_to_translate);
echo $translator->response->jsonResponse;