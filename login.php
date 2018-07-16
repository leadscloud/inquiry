<?php
/**********************************************************
 * Powered by Ray <sbmzhcn@gmail.com>
 * Blog: https://sbmzhcn.github.io/
 * Date: 2018-7-15
 * 如果有任何安装问题，请联系我 QQ：75504026
 **********************************************************/

require('defines.php');
require 'includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

// 检查是否已配置
if (!is_file(ABS_PATH.'/config.php')) {
    header("Location: ". ROOT. 'install.php');
}

$error	=	false;
$message = '';
$error_num = 0;

// 退出登录
$method = isset($_GET['method'])?$_GET['method']:null;
if ($method=='logout') {
    cookie_delete('authcode');
    redirect('login.php');
}

// cookie_delete('authcode');
// cookie_set('testauthcode', "test");

if(user_current(false)){
	redirect('index.php');		
}
	
if($_SERVER['REQUEST_METHOD']=='POST'){
	$username   = isset($_POST['username'])?$_POST['username']:null;
    $userpass   = isset($_POST['password'])?$_POST['password']:null;
    $rememberme = isset($_POST['autologin'])?'forever':null;
	
	
		
	if ($user = user_login($username,$userpass)) { 
            $expire   = $rememberme=='forever' ? 365*86400 : 0;
            cookie_set('authcode',$user['authcode'],$expire);
			//echo '123213';
            redirect('admin/index.php');
        } else {
			$error	=	true;
			$error_num++;
			$message	=	"用户名或密码错误，请重试！";
        }
	if($error_num>=3){
		$message	=	"3次密码错误 ，该用户名已被锁定！";
	}
}


?>
<!--
欢迎使用本系统，源码地址： https://github.com/sbmzhcn/inquiry
Powerred by Ray: https://sbmzhcn.github.io
如果你有什么问题，欢迎在 Github 提问。   
Add at 2018 
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>登陆页面</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel=stylesheet href="admin/css/login.css">
<script src="admin/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="admin/js/jquery.validate.js"></script>
<meta name='robots' content='noindex,nofollow' />
</head>
<body class="bg_c">
<div class=top>
  <div class=gradient></div>
  <div class=white></div>
  <div class=shadow></div>
</div>
<div class=content>
  <h1>系统登陆界面</h1>
  <div class=background></div>
  <div class=wrapper>
    <div class="box">
      <div class="header grey"> <img src="admin/images/lock.png" width=16 height=16 alt="lock" />
        <h3>登陆</h3>
      </div>
      <form method="POST" action="login.php">
        <div class="content no-padding">
        <?php
		if($error){
			echo '<div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; border-left-style: none; border-left-width: initial; border-left-color: initial; border-right-style: none; border-right-width: initial; border-right-color: initial; border-top-left-radius: 0px 0px; border-top-right-radius: 0px 0px; border-bottom-right-radius: 0px 0px; border-bottom-left-radius: 0px 0px; " class="alert warning top .generated"><span class="icon"></span>'.$message.'</div>';
		}
			 
		?>
          <div class="section">
            <label> 用户名 </label>
            <div>
              <input name="username" class="required">
            </div>
          </div>
          <div class="section _100">
            <label> 密码 </label>
            <div>
              <input name="password" type="password" class="required">
            </div>
          </div>
        </div>
        <div class=actions>
          <div class="actions-left" style="margin-top: 8px;">
           
              <input name="autologin" type="checkbox" id="remember" />
               <label for=newsletter>记住我 </label>
          </div>
          <div class=actions-right>
            <button type="submit" class="button">登陆</button>
          </div>
        </div>
      </form>
    </div>
    <div class="shadow"></div>
  </div>
</div>


<script>
(function (a) {
    a.fn.alertBox = function (c, d) {
        var b = a.extend({}, a.fn.alertBox.defaults, d);
        this.each(function () {
            var d = a(this),
                e = "alert " + b.type;
            b.noMargin && (e += " no-margin");
            b.position && (e += " " + b.position);
            e = a('<div style="display:none" class="' + e + ' .generated">' + c + "</div>");
            b.icon && e.prepend(a("<span>").addClass("icon"));
            d.prepend(e);
            a(e).fadeIn()
        })
    };
    a.fn.alertBox.defaults = {
        type: "info",
        position: "top",
        noMargin: !0,
        icon: !1
    }
})(jQuery);
(function (a) {
    a.fn.removeAlertBoxes = function () {
        a(this).find(".alert").fadeOut(function () {
            a(this).remove()
        })
    }
})(jQuery);
$(window).load(function() {
    var a = $("form").validate({
        invalidHandler: function(d, b) {
            var e = b.numberOfInvalids();
            if (e) {
                var c = e == 1 ? "You missed 1 field. It has been highlighted." : "You missed " + e + " fields. They have been highlighted.";
                $(".box .content").removeAlertBoxes();
                $(".box .content").alertBox(c, {
                    type: "warning",
                    icon: true,
                    noMargin: false
                });
                $(".box .content .alert").css({
                    width: "",
                    margin: "0",
                    borderLeft: "none",
                    borderRight: "none",
                    borderRadius: 0
                })
            } else {
                $(".box .content").removeAlertBoxes()
            }
        },
        showErrors: function(c, d) {
            this.defaultShowErrors();
            var b = this;
            $.each(d, function() {
                var f = $(this.element);
                var e = f.parent().find("label.error");

                e.addClass("red");
                e.css("width", parseFloat(e.css("widthExact")) - 10 + "px");
                f.trigger("labeled");
                e.fadeIn()
            })
        },
        submitHandler: function(b) {
            $(form).submit();
        }
    })
	
});
</script>

 <!--[if lt IE 7 ]><script defer src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script> <script defer>window.attachEvent("onload",function(){CFInstall.check({mode:"overlay"})});</script><![endif]-->
</body>
</html>