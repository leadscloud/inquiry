<?php
function currents($url){
	if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'],$query);
        if (!isset($query['method'])) {
            $query = array_merge(array('method' => 'default'),$query);
        }
        $query = '?'.http_build_query($query);
    } else {
        $query = '?method=default';
    }
	
	$parent_file = PHP_FILE.$query;
	$current ='';
	if(strpos($parent_file,$url)!==false)
		$current = !strncasecmp($parent_file,$url,strlen($parent_file)) ? ' class="current"' : '';
	echo $current;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo esc_html(strip_tags(system_head('title')));?> 询盘管理系统</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/logo.svg">
<link rel="stylesheet" type="text/css" href="css/common/reset.css"/>
<link rel="stylesheet" type="text/css" href="css/common/common.css"/>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<link rel="stylesheet" type="text/css" href="css/cpanel.css"/>
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<link rel="stylesheet" type="text/css" href="css/common/icons.css"/>
<link rel="stylesheet" type="text/css" href="css/common/boxy.css"/>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.extend.js"></script>
<script type="text/javascript" src="js/jquery.boxy.js"></script>
<script type="text/javascript" src="js/clipboard.min.js"></script>
<!--<script type="text/javascript" src="js/bootstrap-tooltip.js"></script>-->
<script type="text/javascript" src="js/wcode.js"></script>
<script type="text/javascript" src="js/custom.js"></script>
<script type="text/javascript" src="js/cpanel.js"></script>
</head>

<body class="index-php">
<div id="wrapper">
  <div id="header"> <img id="header-logo" src="/logo.svg" alt="询盘管理系统SQLite版" />
    <h1 id="header-visit"> <a href="/"> <span>询盘管理系统</span> <em>V<?php echo SYS_VERSION; ?></em> </a> </h1>
    <div id="header-menu"><strong><a href="profile.php"><?php echo  $_USER['nickname']?$_USER['nickname']:$_USER['username']; ?></a></strong> | <a href="/login.php?method=logout" onclick="return $(this).logout();">退出</a> </div>
  </div>
  <div id="admin-body">
    <ul id="admin-menu">
      <li id="menu-cpanel" class="head first last current expand"><a href="index.php" class="image"><img src="images/blank.gif" class="os a1" alt="" /></a><a href="index.php" class="text first last">控制面板</a><a href="javascript:;" class="toggle"><br/>
        </a>
        <div class="sub">
          <dl>
            <dt>控制面板</dt>
            <dd<?=currents('/admin/index.php?method=default');?>><a href="index.php">控制面板</a></dd>
            <dd<?=currents('/admin/profile.php?method=default');?>><a href="profile.php">我的配置</a></dd>
          </dl>
        </div>
      </li>
      <li class="separator"><a href="javascript:;"><br/>
        </a></li>
      <li id="menu-posts" class="head first"><a href="inquiry.php" class="image"><img src="images/blank.gif" class="os a2" alt="" /></a><a href="inquiry.php" class="text first">询盘管理</a><a href="javascript:;" class="toggle"><br/>
        </a>
        <div class="sub">
          <dl>
            <dt>询盘管理</dt>
            <dd<?=currents('/admin/inquiry.php?method=default&type=inquiry');?>><a href="inquiry.php?type=inquiry">询盘管理</a></dd>
            <dd<?=currents('/admin/inquiry.php?method=default&read=0');?>><a href="inquiry.php?read=0">未读询盘</a></dd>
            <dd<?=currents('/admin/inquiry.php?method=default&read=1');?>><a href="inquiry.php?read=1">已读询盘</a></dd>
          </dl>
        </div>
      </li>
	  
      <li id="menu-comments" class="head last"><a href="/admin/promotion.php" class="image"><img src="images/blank.gif" class="os c8" alt="" /></a><a href="promotion.php" class="text last">推广</a></li>
      <?php 
	  if(current_user_can('user-list',false)){
	  ?>
      <li class="separator"><a href="javascript:;"><br/>
        </a></li>
      <li id="menu-posts" class="head first last"><a href="/admin/inquiry.php" class="image"><img src="images/blank.gif" class="os a5" alt="" /></a><a href="tools.php" class="text first last">工具</a><a href="javascript:;" class="toggle"><br/>
        </a>
        <div class="sub">
          <dl>
            <dt>工具</dt>
            <dd<?=currents('/admin/tools.php?method=default');?>><a href="tools.php">工具</a></dd>
            <dd<?=currents('/admin/ban.php?method=default');?>><a href="ban.php">屏蔽IP</a></dd>
            <dd<?=currents('/admin/tools.php?method=ping');?>><a href="tools.php?method=ping">Ping服务</a></dd>
            <dd<?=currents('/admin/export.php?method=default');?>><a href="export.php">导出</a></dd>
            <dd<?=currents('/admin/import.php?method=default');?>><a href="import.php">导入</a></dd>
          </dl>
        </div>
      </li>
      <?php 
	  }
	  ?>
      <?php 
	  if(current_user_can('user-list',false)){
	  ?>
      <li class="separator"><a href="javascript:;"><br/>
        </a></li>
      <li id="menu-users" class="head first last"><a href="user.php" class="image"><img src="images/blank.gif" class="os a4" alt="" /></a><a href="user.php" class="text first last">用户管理</a><a href="javascript:;" class="toggle"><br/>
        </a>
        <div class="sub">
          <dl>
            <dt>用户管理</dt>
            <dd<?=currents('/admin/user.php?method=default');?>><a href="user.php">用户管理</a></dd>
            <dd<?=currents('/admin/user.php?method=new');?>><a href="user.php?method=new">添加用户</a></dd>
          </dl>
        </div>
      </li>
      <?php }
	  ?>
<!--      <li id="menu-options" class="head last"><a href="/options/general.php" class="image"><img src="images/blank.gif" class="os a5" alt="" /></a><a href="/options/general.php" class="text last">设置管理</a><a href="javascript:;" class="toggle"><br/>
        </a>
        <div class="sub">
          <dl>
            <dt>设置管理</dt>
            <dd><a href="/options/general.php">常规</a></dd>
            <dd><a href="/options/inquiry.php">留言设置</a></dd>
          </dl>
        </div>
      </li>-->
    </ul>
<script type="text/javascript">$('#admin-menu').init_menu();</script>
    <div id="admin-content">
    	<div id="screen-meta" class="metabox-prefs">
			<div id="contextual-help-wrap" class="hidden">
				<div id="contextual-help-back"></div>
                <div class="metabox-prefs">
                <p>如果有任何问题，请联系 sbmzhcn@gmail.com 或到 Github 上面提问: <a href="https://github.com/leadscloud/inquiry/issues"  target="_blank">https://github.com/leadscloud/inquiry/issues</a> <p>
                <p>请使用chrome或firefox浏览器浏览此页面。后台界面在Chrome 19下测试正常。测试的操作系统为WIN7</p><p>初次登陆，请<a href="profile.php">修改你的密码</a>。退出时请求点击右上角的退出按钮。</p>
                <p>字段说明：</p>
                <div>
                  <code>name</code>
                  <code>email</code>
                  <code>title</code>
                  <code>content</code>
                  <code>country</code>
                  <code>phone</code>
                  <code>address</code>
                  <code>from_company</code>
                </div>
                </div>
				
			</div>
		</div>
        <div id="screen-meta-links">
			<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">
				<a href="#contextual-help-wrap" id="contextual-help-link" class="show-settings">帮助</a>
			</div>
			<div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle" style="visibility: hidden; ">
				<a href="#screen-options-wrap" id="show-settings-link" class="show-settings">Screen Options</a>
			</div>
		</div>
            
