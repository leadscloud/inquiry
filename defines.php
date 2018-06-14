<?php
define('ABS_PATH',dirname(__FILE__));
include ABS_PATH.'/config.php';
//database name
//define('DB_NAME','blog_db.sqlite');
//database prefix
//define('DB_PREFIX','wp');



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

define('DB_PATH',ABS_PATH.'/content/'.DB_NAME);

?>