<?php

/**********************************************************
 * 
 * Powered by Ray <sbmzhcn@gmail.com>
 * Blog: https://leadscloud.github.io/
 * Date: 2019-8-9
 * 如果有任何安装问题，请联系我 QQ：75504026
 * Chrome扩展:域名所属人 https://leadscloud.github.io/serp-analyzer/
 * 
 **********************************************************/

require('defines.php');
require 'includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

// 检查是否已配置
if (!is_file(ABS_PATH . '/config.php')) {
  header("Location: " . ROOT . 'install.php');
}

$error  =  false;
$message = '';
$error_num = 0;

// 退出登录
$method = isset($_GET['method']) ? $_GET['method'] : null;
if ($method == 'logout') {
  cookie_delete('authcode');
  redirect('login.php');
}

// cookie_delete('authcode');
// cookie_set('testauthcode', "test");

if (user_current(false)) {
  redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username   = isset($_POST['username']) ? $_POST['username'] : null;
  $userpass   = isset($_POST['password']) ? $_POST['password'] : null;
  $rememberme = isset($_POST['autologin']) ? 'forever' : null;



  if ($user = user_login($username, $userpass)) {
    $expire   = $rememberme == 'forever' ? 365 * 86400 : 0;
    cookie_set('authcode', $user['authcode'], $expire);
    //echo '123213';
    redirect('admin/index.php');
  } else {
    $error  =  true;
    $error_num++;
    $message  =  "用户名或密码错误，请重试！";
  }
  if ($error_num >= 3) {
    $message  =  "3次密码错误 ，该用户名已被锁定！";
  }
}

?>
<!--
欢迎使用本系统，源码地址： https://github.com/leadscloud/inquiry
Powered by Ray: https://leadscloud.github.io
如果你有什么问题，欢迎在 Github 提问。   

--2021年更新
-->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>登录 - 外贸留言询盘系统</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="/logo.svg">
  <link rel=stylesheet href="admin/css/login.css">
  <link rel=stylesheet href="admin/css/icomoon.css">
  <script src="admin/js/jquery-1.7.1.min.js"></script>
</head>

<body>
  <div class="limiter">
    <div class="container-login">
      <div class="wrap-login p-l-85 p-r-85 p-t-55 p-b-55">
        <form method="POST" action="login.php" class="login-form validate-form flex-sb flex-w">

          <!-- <div class="icon">
            <img src="/logo.svg" alt="外贸留言板系统" width="50" />
          </div> -->
          <span class="login-form-title p-b-32">
            账号登录
          </span>
          <span class="txt1 p-b-11">
            用户名
          </span>
          <div class="wrap-input validate-input m-b-36" data-validate="请填写用户名">
            <input class="input" type="text" name="username">
            <span class="focus-input"></span>
          </div>
          <span class="txt1 p-b-11">
            密码
          </span>
          <div class="wrap-input validate-input m-b-12 <?php echo $error ? "alert-validate" : "$error"; ?>" data-validate="<?php echo empty($message) ? '请输入密码': $message; ?>">
            <span class="btn-show-pass">
              <i class="icon-eye"></i>
            </span>
            <input class="input" type="password" name="password">
            <span class="focus-input"></span>
          </div>
          <div class="flex-sb-m w-full p-b-48">
            <div class="contact-form-checkbox">
              <input class="input-checkbox" id="ckb1" type="checkbox" name="autologin">
              <label class="label-checkbox" for="ckb1">
                记住我
              </label>
            </div>
            <div>
              <a href="#" class="txt3">
                忘记密码?
              </a>
            </div>
          </div>
          <div class="container-login-form-btn">
            <button class="login-form-btn" type="submit">
              登录
            </button>
          </div>
        </form>
      </div>

      <div class="copy-right">
		    <p> © 2010-2021 外贸留言板系统. 版权所有. | Powered by <a href="http://leadscloud.github.io/">sbmzhcn</a>, <a href="https://github.com/leadscloud/inquiry">源码</a></p>
	    </div>
    </div>
  </div>

  <script>
    (function($) {
      "use strict";
      var input = $('.validate-input .input');
      $('.validate-form').on('submit', function() {
          var check = true;
          for (var i = 0; i < input.length; i++) {
              if (validate(input[i]) == false) {
                  showValidate(input[i]);
                  check = false;
              }
          }
          return check;
      });
      $('.validate-form .input').each(function() {
          $(this).focus(function() {
              hideValidate(this);
          });
      });
      function validate(input) {
          if ($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
              if ($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                  return false;
              }
          } else {
              if ($(input).val().trim() == '') {
                  return false;
              }
          }
      }
      function showValidate(input) {
          var thisAlert = $(input).parent();
          $(thisAlert).addClass('alert-validate');
      }
      function hideValidate(input) {
          var thisAlert = $(input).parent();
          $(thisAlert).removeClass('alert-validate');
      }
      var showPass = 0;
      $('.btn-show-pass').on('click', function() {
          if (showPass == 0) {
              $(this).next('input')[0].setAttribute("type", 'text');
              // $(this).next('input').attr('type', 'text');
              $(this).find('i').removeClass('icon-eye');
              $(this).find('i').addClass('icon-eye-slash');
              showPass = 1;
          } else {
              $(this).next('input')[0].setAttribute("type", 'password');
              // $(this).next('input').attr('type', 'password');
              $(this).find('i').removeClass('icon-eye-slash');
              $(this).find('i').addClass('icon-eye');
              showPass = 0;
          }
      });
    }
    )(jQuery);

  </script>
</body>

</html>