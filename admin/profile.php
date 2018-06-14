<?php

// 加载公共文件
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
require_once '../includes/lib/validate.php';
// 查询管理员信息
$_USER = user_current();
// 动作
$referer = referer(PHP_FILE,false);
// 保存我的配置

if (validate_is_post()) {
    $userid    = isset($_USER['uid'])?$_USER['uid']:null;
    $password  = isset($_POST['password1'])?$_POST['password1']:null;
    $password2 = isset($_POST['password2'])?$_POST['password2']:null;
    $nickname  = isset($_POST['nickname'])?$_POST['nickname']:null;
    $email     = isset($_POST['email'])?$_POST['email']:null;
    $url       = isset($_POST['url'])?$_POST['url']:null;
    $desc      = isset($_POST['description'])?$_POST['description']:null;
	$post_per_page = isset($_POST['post_per_page'])?$_POST['post_per_page']:20;
    // 验证email
    validate_check(array(
        array('email',VALIDATE_EMPTY,'Please enter an e-mail address.'),
        array('email',VALIDATE_IS_EMAIL,'You must provide an e-mail address.')
    ));
    // 验证密码
    if ($password || $password2) {
        validate_check('password1',VALIDATE_EQUAL,'Your passwords do not match. Please try again.','password2');
    }

    // 验证通过
    if (validate_is_ok()) {
        $user_info = array(
            'url'  => esc_html($url),
            'email' => esc_html($email),
            'nickname' => esc_html($nickname),
            'description' => esc_html($desc),
			'post_per_page' => $post_per_page
        );
        // 修改暗号
        if ($password) {
            if (isset($_USER['BanChangePassword']) && $_USER['BanChangePassword']=='Yes') {
                alert_echo('Ban Change Password, Please contact the administrator.');
				redirect($referer);
            } else {
                $user_info = array_merge($user_info,array(
                   'password' => md5($password.$_USER['authcode'])
                ));
            }

        }
        if(user_edit($userid,$user_info)!=null)
			alert_echo('用户已更新');
		else
			alert_echo('更新用户信息失败');
		redirect($referer);
    }
} else {
    // 标题
    system_head('title','个人资料');
    system_head('styles', array('css/user'));
    system_head('scripts',array('js/user'));
    system_head('loadevents','user_profile_init');
    $username = isset($_USER['name'])?$_USER['name']:null;
    $nickname = isset($_USER['nickname'])?$_USER['nickname']:null;
    $email    = isset($_USER['email'])?$_USER['email']:null;
    $url      = isset($_USER['url'])?$_USER['url']:null;
    $desc     = isset($_USER['description'])?$_USER['description']:null;
	$post_per_page = isset($_USER['post_per_page'])?$_USER['post_per_page']:20;
    include 'header.php';
    echo '<div class="wrap">';
    echo   '<h2>'.system_head('title').'</h2>';
    echo   '<form action="'.PHP_FILE.'" method="post" name="profile" id="profile">';
    echo     '<fieldset>';
    echo       '<table class="form-table">';
    echo           '<tr>';
    echo               '<th><label for="username">用户名</label></th>';
    echo               '<td><strong>'.$username.'</strong></td>';
    echo           '</tr>';
    echo           '<tr>';
    echo               '<th><label for="nickname">昵称</label></th>';
    echo               '<td><input class="text" id="nickname" name="nickname" type="text" size="20" value="'.$nickname.'" /></td>';
    echo           '</tr>';
    echo           '<tr>';
    echo               '<th><label for="email">邮箱<span class="resume">(必填)</span></label></th>';
    echo               '<td><input class="text" id="email" name="email" type="text" size="40" value="'.$email.'" /></td>';
    echo           '</tr>';
    echo           '<tr>';
    echo               '<th><label for="url">个人主页</label></th>';
    echo               '<td><input class="text" id="url" name="url" type="text" size="60" value="'.$url.'" /></td>';
    echo           '</tr>';
	echo           '<tr>';
    echo               '<th><label for="url">每页显示多少留言</label></th>';
    echo               '<td><input class="text" id="post_per_page" name="post_per_page" type="text" size="10" value="'.$post_per_page.'" /></td>';
    echo           '</tr>';
    echo           '<tr>';
    echo               '<th><label for="description">个人说明</label></th>';
    echo               '<td><textarea class="text" cols="70" rows="5" id="description" name="description">'.$desc.'</textarea>';
    echo                   '<br/><span class="resume">分享一些你的个人简介，这可能会公开显示。</span>';
    echo               '</td>';
    echo           '</tr>';
    echo           '<tr>';
    echo               '<th><label for="password1">密码<span class="resume">(两次)</span></label></th>';
    echo               '<td><input class="text" id="password1" name="password1" type="password" size="20" />';
    echo                   ' <span class="resume">如果你想更改密码，请填写一个新的密码，否则留空。</span>';
    echo                   '<br/><input class="text" id="password2" name="password2" type="password" size="20" /> <span class="resume">再次输入你的新密码。</span>';
    echo                   '<br /><div id="pass-strength-result" class="pass-strength">密码强度指示器</div>';
    echo               '</td>';
    echo           '</tr>';
    echo       '</table>';
    echo     '</fieldset>';
    echo     '<input type="hidden" name="referer" value="'.$referer.'" />';
    echo     '<p class="submit"><button type="submit" class="button">更新资料</button></p>';
    echo   '</form>';
    echo '</div>';
    include 'footer.php';
}


