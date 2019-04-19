<?php
/**********************************************************
 * Powered by Ray <sbmzhcn@gmail.com>
 * Blog: https://sbmzhcn.github.io/
 * Date: 2018-7-15
 * 如果有任何安装问题，请联系我 QQ：75504026
 **********************************************************/

// 显示错误日志用的
error_reporting(-1);
ini_set('display_errors', 1);

define('INSTALL', true);
define('ABS_PATHS',dirname(__FILE__));
error_reporting(1);
include dirname(__FILE__).'/defines.php';

//修改下面的配置以完成安装
$db_name	=	DB_NAME;
$db_prefix	=	DB_PREFIX;
define('DB_FILE',BLOG_ROOT.'/content/'.DB_NAME);
	
//define('DB_NAME',   $db_name);
//define('DB_PREFIX', $db_prefix);
//define('DB_FILE',$db_file);

/**
 * 
 ALTER TABLE table_name
  ADD column_1 column-definition,
      column_2 column-definition,
      ...
      column_n column_definition;
 */

require_once ABS_PATH.'/includes/lib/function.base.php';


$config_exist = is_file(ABS_PATH.'/config.php');

if ($config_exist) {
    if (installed()) redirect(ROOT);
}

$setup = isset($_POST['setup']) ? $_POST['setup'] : 'default';

//
/**
 * 解析 PHP info
 *
 * @return array
 */
function parse_phpinfo() {
    ob_start(); phpinfo(INFO_MODULES); $s = ob_get_contents(); ob_end_clean();
    $s = strip_tags($s, '<h2><th><td>');
    $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $s);
    $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $s);
    $t = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    $r = array(); $count = count($t);
    $p1 = '<info>([^<]+)<\/info>';
    $p2 = '/'.$p1.'\s*'.$p1.'\s*'.$p1.'/';
    $p3 = '/'.$p1.'\s*'.$p1.'/';
    for ($i = 1; $i < $count; $i++) {
        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matchs)) {
            $name = trim($matchs[1]);
            $vals = explode("\n", $t[$i + 1]);
            foreach ($vals AS $val) {
                if (preg_match($p2, $val, $matchs)) { // 3cols
                    $r[$name][trim($matchs[1])] = array(trim($matchs[2]), trim($matchs[3]));
                } elseif (preg_match($p3, $val, $matchs)) { // 2cols
                    $r[$name][trim($matchs[1])] = trim($matchs[2]);
                }
            }
        }
    }
    return $r;
}

/**
 * 取得PHPINFO
 *
 * @param int $info
 * @return mixed|string
 */
function system_phpinfo($info = INFO_ALL) {
    /**
     * callback function to eventually add an extra space in passed <td class="v">...</td>
     * after a ";" or "@" char to let the browser split long lines nicely
     */
    function _system_phpinfo_v_callback($matches) {
        $matches[2] = preg_replace('/(?<!\s)([;@])(?!\s)/', "$1 ", $matches[2]);
        return $matches[1] . $matches[2] . $matches[3];
    }
    ob_start(); phpinfo($info);
    $output = preg_replace(array('/^.*<body[^>]*>/is', '/<\/body[^>]*>.*$/is'), '', ob_get_clean(), 1);

    $output = preg_replace('/width="[0-9]+"/i', 'width="100%"', $output);
    $output = str_replace('<table border="0" cellpadding="3" width="100%">', '<table class="phpinfo">', $output);
    $output = str_replace('<hr />', '', $output);
    $output = str_replace('<tr class="h">', '<tr>', $output);
    $output = str_replace('<a name=', '<a id=', $output);
    $output = str_replace('<font', '<span', $output);
    $output = str_replace('</font', '</span', $output);
    $output = str_replace(',', ', ', $output);
    // match class "v" td cells an pass them to callback function
    return preg_replace_callback('%(<td class="v">)(.*?)(</td>)%i', '_system_phpinfo_v_callback', $output);
}

switch($setup) {
	case 'install':
			$sql = "
DROP TABLE IF EXISTS #@_blog;
CREATE TABLE #@_blog (
  gid mediumint(8)  NOT NULL PRIMARY KEY,
  title varchar(255) NOT NULL default '',
  longtitle varchar(255) default '',
  date bigint(20) NOT NULL,
  content longtext NOT NULL,
  excerpt longtext NOT NULL,
  alias VARCHAR(200) NOT NULL DEFAULT '',
  author int(10) NOT NULL default '1',
  sortid tinyint(3) NOT NULL default '-1',
  type varchar(20) NOT NULL default 'blog',
  views mediumint(8)  NOT NULL default '0',
  comnum mediumint(8)  NOT NULL default '0',
  password varchar(255) NOT NULL default ''
);
DROP TABLE IF EXISTS #@_user;
CREATE TABLE #@_user (
  uid INTEGER PRIMARY KEY,
  username varchar(32) NOT NULL default '',
  password varchar(64) NOT NULL default '',
  status tinyint(1) NOT NULL DEFAULT '0',
  registered timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  authcode char(36) NOT NULL,
  email varchar(60) NOT NULL default ''
);
DROP TABLE IF EXISTS #@_user_meta;
CREATE TABLE #@_user_meta (
  `mid` INTEGER PRIMARY KEY,
  `uid` int(10) NOT NULL DEFAULT '0',
  `key` char(50) NOT NULL,
  `value` longtext NOT NULL,
  `type` varchar(10) NOT NULL
);
DROP TABLE IF EXISTS #@_inquiry;
CREATE TABLE #@_inquiry (
  `id` INTEGER PRIMARY KEY,
  `title`	varchar(255),
  `name`	varchar(255),
  `phone`	varchar(255),
  `email`	varchar(255),
  `company`	varchar(255),
  `content`	longtext NOT NULL,
  `time`	datetime,
  `website`	varchar(255),
  `ip`	    varchar(255),
  `country`	varchar(255),
  `address`	varchar(255),
  `read`	int(1) NOT NULL DEFAULT '0',
  `status`	tinyint(1) NOT NULL DEFAULT '0',
  `type` char(20) NOT NULL DEFAULT 'inquiry',
  `from_company` varchar(200),
  `browser_name`	varchar(255),
  `browser_version`	varchar(255),
  `browser_platform`	varchar(255),
  `lang`	varchar(255),
  `user_agent`	varchar(255),
  `timezone_offset` int(2)
);
DROP TABLE IF EXISTS #@_inquiry_meta;
CREATE TABLE #@_inquiry_meta (
  `inquiryid` INTEGER,
  `key` char(50) NOT NULL,
  `value` longtext NOT NULL,
  `type` varchar(10)
);
";
        $db_name = isset($_POST['dbname'])?$_POST['dbname']:'#inquiry_system.sqlite.php';
		$db_prefix = isset($_POST['prefix'])?$_POST['prefix']:'';
		$bing_api = isset($_POST['bing_api'])?$_POST['bing_api']:'';
        $akismet_api  = isset($_POST['akismet_api'])?$_POST['akismet_api']:'';
        $adminname = isset($_POST['adminname'])?$_POST['adminname']:'';
        $password  = isset($_POST['password1'])?$_POST['password1']:'';
        $email     = isset($_POST['email'])?$_POST['email']:'';
		
		$configs = file(ABS_PATH.'/config.sample.php');
		foreach ($configs as $num => $line) {
			switch(substr($line,0,19)) {
                case "define('DB_NAME','d":
                    $configs[$num] = str_replace("database_name_here", $db_name, $line);
                    break;
				case "define('DB_PREFIX',":
					$configs[$num] = str_replace("database_prefix_here", $db_prefix, $line);
					break;
				case "define('BING_TRANSL":
					$configs[$num] = str_replace("bing_account_key_here", $bing_api, $line);
					break;
				case "define('Akismet_API":
					$configs[$num] = str_replace("akismet_api_key_here", $akismet_api, $line);
					break;
			}
        }		
        
        
        $db_path = ABS_PATHS . '/content/';
        $dbfile = ABS_PATHS . '/content/'. $db_name;

        if (!file_exists($db_path)) {
            if (!mkdir($db_path)) {
                die("创建数据库目录失败，请手动创建！". $db_path);
            }
        }

        if(!is_writable(ABS_PATHS . '/content/')) {
            $error_html = "<p>安装失败，数据库文件目录 $dbfile 没有权限写入。</p>";
            install_wrapper($error_html);
            break;
        }    

        // 检查是否具有写入权限
		if ($writable = is_writable(ABS_PATH.'/')) {
			$config = implode('', $configs);
			file_put_contents(ABS_PATH.'/config.php', $config);
        } else { 
            $error_html = "<p>安装失败，配置文件 $ABS_PATH.'/' 没有权限写入。</p>";
            install_wrapper($error_html);
            break;
        }
        
        require_once ABS_PATH.'/includes/lib/function.base.php';
        $db	=	new db();

		$db->create_db($dbfile);
        $db->sqlite($dbfile,$db_prefix);
        
        $sql = preg_replace('/#@/',$db_prefix,$sql);

        // print_r($sql);

        $db->exec($sql);
        // print_r($db);
		

		$data	=	array(
						'url'  => esc_html(HTTP_HOST),
						'nickname' => esc_html($adminname),
						'roles' => 'ALL',
						'Administrator' => 'Yes'
					);
		
        $html = '<div class="main"><p>留言系统安装成功！</p><p>请检查根目录下的 reset_admin.php 和 reset_password.php 文件，如果存在请删除！</p><p class="step"><a href="'.ROOT.'" class="button">&laquo; 进入</a></p></div>';
        
        $userid = $db->insert($db_prefix.'_user',array(
            'username' => $adminname,
            'password' => $password,
            'email' => $email,
            'authcode' => '',
            'status' => 0,
            'registered' => date('Y-m-d H:i:s',time()),
        ));
        $authcode = authcode($userid);
        // echo "authcode[".$authcode."]";
        // echo "pass[".$password."]";
        $user_info = array(
            'password' => md5($password.$authcode),
            'authcode' => $authcode,
        );
        // print_r($user_info);

        // 更新下密码
        $db->update($db_prefix.'_user', $user_info, array('uid' => $userid));

        // 添加权限
        foreach ($data as $key=>$value) {
            $db->insert($db_prefix.'_user_meta',array(
                'uid' => $userid,
                'key'    => $key,
                'value'  => $value,
                'type'   => "string",
            ));
        }

        //删除一些文件
        unlink('reset_admin.php');
        unlink('reset_password.php');
        
        install_wrapper($html);

		// if(user_add($adminname,$password,$email,$data)){
			
		// }
		break;
    case 'config':
		$html = '<form action="'.PHP_FILE.'" method="post" name="setup_cfg" id="setup_cfg">';
        if (!$config_exist) {
			//
        }
        $html.=     '<p>请提供以下信息以供安装使用，此系统目前使用的数据库为SQLITE</p>';
        $html.=     '<table class="data-table">';
        $html.=         '<thead><tr><th colspan="3">必填信息</th></tr></thead>';
        $html.=         '<tbody>';
        $html.=             '<tr><th class="w150"><label for="prefix">数据库名</label></th><td><input class="text" type="text" name="dbname" id="dbname" value="#inquiry_system.sqlite.php" /></td><td></td></tr>';
        $html.=             '<tr><th class="w150"><label for="prefix">数据表前缀</label></th><td><input class="text" type="text" name="prefix" id="prefix" value="wp" /></td><td></td></tr>';
		$html.=             '<tr><th class="w150"><label for="bing_api">Bing 翻译API Key</label></th><td><input class="text" type="text" name="bing_api" id="bing_api" /></td><td><a href="https://datamarket.azure.com/dataset/1899a118-d202-492c-aa16-ba21c33c06cb" target="_blank">注册地址</a></td></tr>';
		$html.=             '<tr><th class="w150"><label for="akismet_api">Akismet API Key</label></th><td><input class="text" type="text" name="akismet_api" id="akismet_api" /></td><td><a href="https://akismet.com/signup/" target="_blank">注册地址</a></td></tr>';
        $html.=             '<tr><th><label for="adminname">用户名</label></th><td><input class="text" type="text" name="adminname" id="adminname" /></td><td>管理员账号.</td></tr>';
        $html.=             '<tr>';
        $html.=                 '<th><label for="password1">密码</label></th>';
        $html.=                 '<td><input class="text" type="password" name="password1" id="password1" /></td>';
        $html.=                 '<td></td>';
        $html.=             '</tr>';
        $html.=             '<tr><th><label for="email">邮箱</label></th><td><input class="text" type="text" name="email" id="email" /></td><td>Double-check your email address before continuing.</td></tr>';
        $html.=         '</tbody>';
        $html.=     '</table>';
        $html.=     '<p>以上部分配置可以在根目录下的config.php里修改。</p>';
        $html.=     '<p class="buttons">';
        $html.=         '<input type="hidden" name="setup" value="install" />';
        $html.=         '<button type="submit" class="button">安装</button>';
        $html.=     '</p>';
        $html.= '</form>';
        install_wrapper($html);
		break;
	default:
		$error_level = error_reporting(1);
		$html = '<form action="'.PHP_FILE.'" method="post" name="setup" id="setup">';
        $html.=     '<table class="data-table">';
        $html.=         '<thead><tr><th colspan="3">System Information</th></tr></thead>';
        $html.=         '<tbody>';
        $html.=             '<tr><td>Server OS</td><td>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</td></tr>';
        $html.=             '<tr><td>Server Software</td><td>'.$_SERVER['SERVER_SOFTWARE'].'</td></tr>';
        $html.=             '<tr><td>Server API</td><td>'.PHP_SAPI.'</td></tr>';
        $html.=         '</tbody>';
        $html.=     '</table>';

        $html.=     '<table class="data-table">';
        $html.=         '<thead><tr><th colspan="3">Required Settings</th></tr></thead>';
        $html.=         '<tbody>';
        $html.=             '<tr class="thead"><th>Test</th><th class="w100">Require</th><th class="w150">Current</th></tr>';
        $html.=             '<tr><td>PHP Version</td><td>4.3.3+</td><td>'.test_result(version_compare(PHP_VERSION,'4.3.3','>')).'&nbsp; '.PHP_VERSION.'</td></tr>';
        $html.=             '<tr><td>DB Driver</td><td>SQLite 2.8.0+<br />MySQL 4.1.0+</td><td>';
        // sqlite
        $phpinfo = parse_phpinfo();
        $sqlite  = isset($phpinfo['pdo_sqlite']) ? array_shift($phpinfo['pdo_sqlite']) == 'enabled' : false;
        if ($r = class_exists('SQLite3')) {
            $version = SQLite3::version();
            $html.=             test_result($r).'&nbsp; SQLite '.$version['versionString'];
        } elseif (extension_loaded('pdo_sqlite') && $sqlite) {
            $version = $phpinfo['pdo_sqlite']['SQLite Library'];
            $html.=             test_result($sqlite).'&nbsp; SQLite '.$version;
        } elseif ($r = function_exists('sqlite_libversion')) {
            $html.=             test_result($r).'&nbsp; SQLite '.sqlite_libversion();
        } else {
            $html.=             test_result(false).'&nbsp; SQLite '.__('Not Supported');
        }
        $html.=                 '<br />';
        // mysql
        if ($r = function_exists('mysql_get_client_info')) {
            $html.=             test_result($r).'&nbsp; MySQL '.mysql_get_client_info();
        } elseif ($r = function_exists('mysqli_get_client_info')) {
            $html.=             test_result($r).'&nbsp; MySQL '.mysqli_get_client_info();
        } else {
            $html.=             test_result(false).'&nbsp; MySQL '.__('Not Supported');
        }
        $html.=             '</td></tr>';
        $html.=             '<tr><td>GD Library</td><td>2.0.0+</td><td>'.test_result(function_exists('gd_info')).'&nbsp; '.(function_exists('gd_info') ? GD_VERSION : 'Not Supported').'</td></tr>';
        $html.=             '<tr><td>Iconv Support</td><td>2.0.0+</td><td>'.test_result(function_exists('iconv')).'&nbsp; '.(function_exists('iconv') ? ICONV_VERSION : 'Not Supported').'</td></tr>';
        $html.=             '<tr><td>'.'Multibyte Support'.'</td><td>Support</td><td>'.test_result(extension_loaded('mbstring')).'&nbsp; '.(extension_loaded('mbstring') ? 'mbstring' : 'Not Supported').'</td></tr>';
        $html.=             '<tr><td>Remote URL Open</td><td>Support</td><td>'.test_result(function_exists('curl_init')).'&nbsp; '.(function_exists('curl_init') ? 'Support' : 'Not Supported').'</td></tr>';
        $html.=             '<tr><td>Database Writable</td><td>Writable</td><td>'.test_result(is_writable(ABS_PATHS . '/content/')).'&nbsp; '.(is_writable(ABS_PATHS . '/content/') ? 'Support' : 'Not Supported').'</td></tr>';
        $html.=         '</tbody>';
        $html.=     '</table>';
        //$html.=     system_phpinfo(INFO_CONFIGURATION | INFO_MODULES);
        $html.=     '<p class="buttons">';
        $html.=         '<input type="hidden" name="setup" value="config" />';
        $html.=         '<button type="submit" class="button">继续</button>';
        //$html.=         '<button type="button" rel="phpinfo" class="button">显示 PHP Information</button>';
        $html.=     '</p>';
        $html.= '</form>';
        error_reporting($error_level);
        install_wrapper($html);
        break;
}
function install_wrapper($html) {
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<title>留言系统安装by Ray</title>';
	echo '<link href="'.ROOT.'admin/css/common/reset.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.ROOT.'admin/css/common/common.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.ROOT.'admin/css/style.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.ROOT.'admin/css/install-dev.css" rel="stylesheet" type="text/css" />';
    echo '</head><body>';
    echo '<h1 id="logo">留言系统安装</h1>';
    echo $html.'</body></html>';
}
?>