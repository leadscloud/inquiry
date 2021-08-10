<?php
//database name
define('DB_NAME','database_name_here');
//database prefix
define('DB_PREFIX','database_prefix_here');
//bing translate api key
define('BING_TRANSLATE_KEY', 'bing_account_key_here');
//Akismet API Key
define('Akismet_API_Key','akismet_api_key_here');
//system root
define('BLOG_ROOT',dirname(__FILE__));

// 由于chrome隐私策略，提交成功后的跳转最好使用history.back,一般也不会出问题
define('SUBMIT_DONE_REDIRECT_JS', 'history.back();');
?>