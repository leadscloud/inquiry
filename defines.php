<?php
/**************************************************************
 * 
 * 外贸留言板系统 
 * 
 * 如果你使用此系统，请保留版本声明。
 * 
 * Copyright (c) Ray
 * Email: <sbmzhcn@gmail.com>
 * Website: https://leadscloud.github.io/
 * 
 ***************************************************************/

define('ABS_PATH',dirname(__FILE__));


defined('DB_NAME') or define('DB_NAME', '#inquiry_system.sqlite.php');
defined('DB_PREFIX') or define('DB_PREFIX', 'wp');

// Turn on or off all error reporting
error_reporting(1);



// 系统信息
if(version_compare(PHP_VERSION,'6.0.0','<') ) {
    @set_magic_quotes_runtime(0);
}
defined('E_STRICT') or define('E_STRICT',2048);
define('IS_CGI',!strncasecmp(PHP_SAPI,'cgi',3) ? 1 : 0 );
define('IS_WIN',DIRECTORY_SEPARATOR == '\\' );
define('IS_CLI',PHP_SAPI=='cli' ?  1 : 0);
// 当前文件名  ,  还没用到过
if(!defined('PHP_FILE')) {
    if (IS_CLI) {
        define('PHP_FILE',$argv[0]);
    } elseif(IS_CGI) {
        //CGI/FASTCGI模式下
        $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
        define('PHP_FILE', rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
    } else {
        define('PHP_FILE', rtrim($_SERVER["SCRIPT_NAME"],'/'));
    }
}
//system root
//define('ROOT',dirname(__FILE__));

//define('BLOG_ROOT',str_replace('\\','/',substr(dirname(__FILE__),0,-strlen(substr(realpath('.'),strlen(dirname(__FILE__))))+1)));

define('ROOT',str_replace('\\','/',substr(dirname(PHP_FILE),0,-strlen(substr(realpath('.'),strlen(ABS_PATH)))+1)));

if (!defined("INSTALL") && !is_file(ABS_PATH.'/config.php')) {
    header("Location: ". ROOT. 'install.php');
}

include ABS_PATH.'/config.php';

define('DB_PATH',ABS_PATH.'/content/'.DB_NAME);

if (!defined("INSTALL") && !file_exists(DB_PATH)) {
    die("Database file is not exists! DB Path: " . DB_PATH);
}

?>