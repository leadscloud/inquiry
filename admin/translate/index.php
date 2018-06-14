<?php
require_once('config.inc.php');
require_once('class/ServicesJSON.class.php');
require_once('class/MicrosoftTranslator.class.php');


$translator = new MicrosoftTranslator(ACCOUNT_KEY);
$selectbox = array('id'=> 'txtLang','name'=>'txtLang');
$translator->getLanguagesSelectBox($selectbox);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" " http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd ">
<html xmlns=" http://www.w3.org/1999/xhtml ">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Microsoft Translator Demo</title> 
<style>
div.jquery-ajax-loader {
	background: #FFFFFF url(img/ajax-loader.gif) no-repeat 50% 50%;
	opacity: .6;
	width:250px !important;
}

div.showdata{
    width:250px;
}
.bgblack{
    background: white
}
.bgwhite {
    background: #FFFFFF 
}
.black {
    color:black;
}
.pl10{
    padding-left:10px;
}
.width500 {
    width:500px;
}
.bdr {
 border:1px solid black;
}
</style>
<script src="http://code.jquery.com/jquery-1.5.js"></script>
<script src="js/jquery.ajaxLoader.js"></script>
<script src="js/json-jquery.js" type="text/javascript"></script> 

</head>
<body class="bgblack">
<h1>Demo For Microsoft Translator PHP Wrapper</h1>
<form>
<textarea rows="2" cols="60" name="txtString" id="txtString" value="Hello World" ></textarea><br/>
<?php echo $translator->response->languageSelectBox; ?>
<a class="black pl10" href="#" id="getdata-button">Translate</a>
<div id="loader">&nbsp;</div>
<div class="bgwhite width500" id="showdata"></div>
</form>

</body>
</html> 