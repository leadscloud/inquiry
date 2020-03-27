<?php
/**
 */
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
$_USER = user_current();
// 权限验证
//current_user_can('tools');

$referer = referer(PHP_FILE,false);

$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

system_head('title','工具');

include 'header.php';

switch ($method) {
	case 'box':
		system_head('title','百宝箱');
		echo '<div class="wrap tools">';
		echo   '<h2>'.system_head('title').'</h2>';
		echo	'<iframe src="https://m.chouti.com/all/hot" style="width:100%;height:100%;min-height:916px;" frameborder="0" scrolling="no"></iframe>';
		echo '</div>';
		break;
	case 'ping':
		// 标题
		system_head('title','Google ping服务');
		
		echo '<div class="wrap tools">';
		echo   '<h2>'.system_head('title').'</h2>';
		echo   '<form action="'.PHP_FILE.'" method="post" name="tools" id="tools">';
		echo     '<fieldset>';
		echo       '<table class="form-table">';
		echo           '<tr>';
		echo               '<th><label for="site_name">网站名称</label></th>';
		echo               '<td><input type="text" name="site_name" value="" class="bold_field text"></td>';
		echo           '</tr>';
		echo           '<tr>';
		echo               '<th><label for="site_url">网站URL</label></th>';
		echo               '<td><input type="text" name="site_url" value="http://" class="bold_field text" size="60"></td>';
		echo           '</tr>';
		echo           '<tr>';
		echo               '<th><label for="rss_url">XML feed (选项)</label></th>';
		echo               '<td><input type="text" name="rss_url" value="http://" class="bold_field text" size="60"></td>';
		echo           '</tr>';
		echo       '</table>';
		echo     '</fieldset>';
		echo     '<input type="hidden" name="referer" value="'.$referer.'" />';
		echo     '<p class="submit"><button type="submit" class="button">开始ping ...</button></p>';
		echo   '</form>';
		echo '</div>';
		break;
	case 'cleandb':
		$db = get_conn();
		//$db->exec('DELETE FROM #@_inquiry WHERE 1');
		echo '<div class="wrap tools">';
		echo   '<h2>'.system_head('title').'</h2>';
		echo	'<div class="clear"></div>';
		echo	'<p>数据库清空功能禁用。</p>';
		echo '</div>';
		break;
	default:

        $useragent = $_SERVER['HTTP_USER_AGENT'];
        $ip        = $_SERVER['REMOTE_ADDR'];
        // $request_url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=' . $ip . '&format=json';
        // $info      = json_decode(file_get_contents($request_url), false);
        // if (isset($info->ret) && $info->ret == 1) {
        //     if ($info->province != $info->city) {
        //         $address = $info->country . "," . $info->province . "(" . $info->city . ")  " . $info->district . "  " . $info->desc;
        //     } else {
        //         $address = $info->country . "," . $info->province . "  " . $info->district . "  " . $info->desc;
        //     }
        // } else
        //     $address = '地球';

		echo '<div class="wrap tools">';
		echo   '<h2>'.system_head('title').'</h2>';
		echo	'<div class="clear"></div>';
		echo	'<p>你的IP地址为：<b>'.getip().'</b></p>';
		// echo	'<p>你来自：<b>'.$address.'</b></p>';
		echo	'<p>你的浏览器为：<b>'.browser($useragent).'</b></p>';
		echo	'<p>你的操作系统为：<b>'.os($useragent).'</b></p>'; 
		echo    '<form action="'.PHP_FILE.'?method=cleandb" method="post" name="tools" id="tools">';
		echo       '<button type="submit" class="button">清空数据库</button>';
		echo    '</form>';
		echo '</div>';
		break;
		break;
}
include 'footer.php';

    

function googleping_send ($blogname, $webaddress, $urladdress, $rssaddress, $categoryname) {
	$url="http://blogsearch.google.com/ping/RPC2";
	$trackback_url=parse_url($url);
	$out="POST {$trackback_url['path']}".($trackback_url['query'] ? '?'.$trackback_url['query'] : '')." HTTP/1.0\r\n";
	$out.="Host: {$trackback_url['host']}\r\n";
	$out.="Content-Type: text/xml; charset=utf-8\r\n";
 
	$query_string="<?xml version=\"1.0\"?>\r\n<methodCall>\r\n<methodName>weblogUpdates.extendedPing</methodName>\r\n<params>\r\n<param>\r\n<value>{$blogname}</value>\r\n</param>\r\n<param>\r\n<value>{$webaddress}</value>\r\n</param>\r\n<param>\r\n<value>{$urladdress}</value>\r\n</param>\r\n<param>\r\n<value>{$rssaddress}</value>\r\n</param>\r\n<param>\r\n<value>{$categoryname}</value>\r\n</param>\r\n</params>\r\n</methodCall>";
 
	$out.='Content-Length: '.strlen($query_string)."\r\n";
	$out.="User-Agent: Bo-Blog\r\n\r\n";
	$out.=$query_string;
	if ($trackback_url['port']=='') $trackback_url['port']=80;
	$fs=fsockopen($trackback_url['host'], $trackback_url['port'], $errno, $errstr, 10);
	if (!$fs) return false;
	fputs($fs, $out);
	$http_response = '';
	while(!feof($fs)) {
		$http_response .= fgets($fs, 128);
	}
	@fclose($fs);
	return true;
}


function browser($ua)
{
    if (stripos($ua, "Googlebot")) {
        $browser = "谷歌蜘蛛";
    } elseif (stripos($ua, "Baiduspider") !== false) {
        $browser = "百度蜘蛛";
    } elseif (stripos($ua, "Yahoo!") !== false) {
        $browser = "雅虎蜘蛛";
    } elseif (stripos($ua, "bingbot")) {
        $browser = "必应蜘蛛";
    } elseif (stripos($ua, "YRSpider")) {
        $browser = "云壤蜘蛛";
    } elseif (stripos($ua, "Yeti") !== false) {
        $browser = "Naver蜘蛛";
    } elseif (stripos($ua, "Maxthon")) {
        if (stripos($ua, "AppleWebKit")) {
            $browser = "遨游浏览器(极速模式)";
        } elseif (stripos($ua, "Trident")) {
            $browser = "遨游浏览器(兼容模式)";
        } elseif (stripos($ua, "MAXTHON 2.0")) {
            $browser = "遨游浏览器2.0";
        }
    } elseif (stripos($ua, "Firefox")) {
        $browser = "火狐浏览器";
    } elseif (stripos($ua, "Opera") == 0 && stripos($ua, "Presto")) {
        $browser = "Opera";
    } elseif (stripos($ua, "BIDUBrowser")) {
        if (stripos($ua, "Trident")) {
            $browser = "百度浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebKit")) {
            $browser = "百度浏览器(极速模式)";
        }
    } elseif (stripos($ua, "Ruibin")) {
        $browser = "瑞影浏览器";
    } elseif (stripos($ua, "qihu theworld")) {
        if (stripos($ua, "Trident")) {
            $browser = "世界之窗浏览器";
        } elseif (stripos($ua, "AppleWebKit")) {
            $browser = "世界之窗浏览器(极速模式)";
        }
    } elseif (stripos($ua, "MetaSr")) {
        if (stripos($ua, "Trident")) {
            $browser = "搜狗高速浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebKit")) {
            $browser = "搜狗高速浏览器(极速模式)";
        }
    } elseif (stripos($ua, "LBBROWSER")) {
        if (stripos($ua, "Trident")) {
            $browser = "猎豹浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebKit")) {
            $browser = "猎豹浏览器(极速模式)";
        }
    } elseif (stripos($ua, "YLMFBR")) {
        $browser = "115浏览器";
    } elseif (stripos($ua, "QQBrowser")) {
        if (stripos($ua, "Trident")) {
            $browser = "QQ浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebKit")) {
            $browser = "QQ浏览器(极速模式)";
        }
    } elseif (stripos($ua, "TencentTraveler")) {
        $browser = "腾讯TT浏览器";
    } elseif (stripos($ua, "TaoBrowser")) {
        if (stripos($ua, "Trident")) {
            $browser = "淘宝浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebkit")) {
            $browser = "淘宝浏览器(极速模式)";
        }
    } elseif (stripos($ua, "CoolNovo")) {
        $browser = "枫树浏览器";
    } elseif (stripos($ua, "SaaYaa")) {
        $browser = "闪游浏览器";
    } elseif (stripos($ua, "360SE")) {
        $browser = "360安全浏览器";
    } elseif (stripos($ua, "360EE")) {
        if (stripos($ua, "Trident")) {
            $browser = "360极速浏览器(兼容模式)";
        } elseif (stripos($ua, "AppleWebkit")) {
            $browser = "360极速浏览器(极速模式)";
        }
    } elseif (stripos($ua, "Konqueror")) {
        $browser = "Konqueror";
    } elseif (stripos($ua, "Chrome")) {
        $browser = "谷歌浏览器";
    } elseif (stripos($ua, "Safari")) {
        $browser = "Safari";
    } elseif (stripos($ua, "MSIE")) {
        $ver     = explode(";", substr($ua, stripos($ua, "MSIE") + 5, 4));
        $ver     = $ver[0];
        $browser = "IE " . $ver;
    } elseif (stripos($ua, "UCWEB")) {
        $browser = "UCWEB浏览器";
    } elseif (stripos($ua, "WAP")) {
        $browser = "Mobile浏览器";
    } else {
        $browser = $ua;
    }
    if ($browser == '')
        $browser = $ua;
    return $browser;
}
function os($ua)
{
    $os = "";
    if (stripos($ua, "Googlebot")) {
        $os = "谷歌蜘蛛";
    } elseif (stripos($ua, "Baiduspider") !== false) {
        $os = "百度蜘蛛";
    } elseif (stripos($ua, "Yahoo!") !== false) {
        $os = "雅虎蜘蛛";
    } elseif (stripos($ua, "bingbot")) {
        $os = "必应蜘蛛";
    } elseif (stripos($ua, "YRSpider")) {
        $os = "云壤蜘蛛";
    } elseif (stripos($ua, "Yeti") !== false) {
        $os = "Naver蜘蛛";
    } elseif (stripos($ua, "Windows NT")) {
        switch (substr($ua, stripos($ua, "Windows NT") + 11, 3)) {
            case 5.0: {
                $os = "Windows 2000";
                break;
            }
            case 5.1: {
                $os = "Windows XP";
                break;
            }
            case 5.2: {
                $os = "Windows 2003";
                break;
            }
            case 6.0: {
                $os = "Windows Vista/2008";
                break;
            }
            case 6.1: {
                $os = "Windows 7";
                break;
            }
            case 6.2: {
                $os = "Windows 8";
                break;
            }
            default: {
                $os = "Windows";
                break;
            }
        }
        if (stripos($ua, "WOW64")) {
            $os .= "(X64)";
        } else {
            $os .= "(X86)";
        }
    } elseif (stripos($ua, "Android")) {
        $os = substr($ua, stripos($ua, "Android"), 11);
    } elseif (stripos($ua, "Linux")) {
        if (stripos($ua, "i686")) {
            $os = "Linux X86";
        } else {
            $os = "Linux";
        }
        if (stripos($ua, "X11")) {
            $os .= "(X Window)";
        }
    } elseif (stripos($ua, "Macintosh")) {
        $os = "Mac";
    } elseif (stripos($ua, "IOS")) {
        $os = "iOS";
    } elseif (stripos($ua, "ZTE")) {
        $os = "ZTE";
    } elseif (stripos($ua, "Windows 98")) {
        $os = "Windows 98";
    } else {
        $os = "未知系统";
    }
    return $os;
}

