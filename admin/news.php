<?php

defined('ADMIN_PATH') or define('ADMIN_PATH', dirname(__FILE__));
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
system_head('title','浏览新闻');
$_USER = user_current();
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
include ADMIN_PATH.'/header.php';

/* 
 ======================================================================
 lastRSS usage DEMO 3 - Simple RSS agregator
 ----------------------------------------------------------------------
 This example shows, how to create simple RSS agregator
     - create lastRSS object
    - set transparent cache
    - show a few RSS files at once
 ======================================================================
*/

function ShowOneRSS($url) {
    global $rss;
    if ($rs = $rss->get($url)) {
        echo "<big><b><a href=\"$rs[link]\" target=\"_blank\">$rs[title]</a></b></big><br />\n";
        //echo "$rs[description]<br />\n";

            echo "<ul>\n";
            foreach ($rs['items'] as $item) {
                echo "\t<li><a href=\"$item[link]\" target=\"_blank\">$item[title]</a></li>\n";
            }
            if ($rs['items_count'] <= 0) { echo "<li>Sorry, no items found in the RSS file :-(</li>"; }
            echo "</ul>\n";
    }
    else {
        echo "Sorry: It's not possible to reach RSS file $url\n<br />";
        // you will probably hide this message in a live version
    }
}

// ===============================================================================

// include lastRSS
include "../includes/lib/rss.php";

// List of RSS URLs
$rss_left = array(
    'http://feed.u148.net/'
);
$rss_right = array(
    'http://dig.chouti.com/feed.xml'
);

// Create lastRSS object
$rss = new lastRSS;

// Set cache dir and cache time limit (5 seconds)
// (don't forget to chmod cahce dir to 777 to allow writing)
$rss->default_cp = 'utf-8';
$rss->cache_dir = './temp';
$rss->cache_time = 1200;



$db = get_conn();

        echo '<div class="wrap">';
        echo    '<h2>新闻中心</h2>';
		echo    '<div class="clear"></div>';
        echo    '<div class="container  newsbox">';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="点击切换"><br/></a>';
        echo            '<h3>网易新闻中心</h3>';
        echo            '<div class="inside right-now news163 comments">';
        //echo                '<div class="content 163news">';

echo '<iframe frameborder="0" scrolling="no" src="http://news.163.com/special/0001127H/163mail09.html?color=138144" style="width:100%; height:330px;"></iframe>';
        //echo				'</div>';
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="点击切换"><br/></a>';
        echo            '<h3>RSS</h3>';
        echo            '<div class="inside rss-widget">';
        foreach ($rss_left as $url) {
    		ShowOneRSS($url);
		}
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<div class="clear"><br/></div>';
        echo    '</div>';
        echo    '<div class="container newsbox">';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="Click to toggle"><br/></a>';
        echo            '<h3>RSS</h3>';
        echo            '<div class="inside rss-widget">';
		foreach ($rss_right as $url) {
    		ShowOneRSS($url);
		}
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<div class="clear"><br/></div>';
        echo    '</div>';
        echo '</div>';
        // 加载尾部
        include ADMIN_PATH.'/footer.php';
?>