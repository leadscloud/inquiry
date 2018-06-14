<?php
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
require_once '../includes/lib/validate.php';
$_USER = user_current();
$referer = referer(PHP_FILE);
//system_head('title',  'Users'));
//system_head('styles', array('css/user'));
//system_head('scripts',array('js/user'));
// 动作

/**
 * 权限列表
 *
 * @return array
 */
function system_purview($data=null) {
    global $LC_Purview;
    $hl = '<div class="role-list">';
    foreach ((array) $LC_Purview as $k=>$pv) {
        $title = $pv['_LABEL_']; unset($pv['_LABEL_']);
        $roles = null; $parent_checked = ' checked="checked"';
        foreach ($pv as $sk=>$spv) {
            if ($data == 'ALL') {
                $checked = ' checked="checked"';
            } else {
                $checked = instr($sk, $data)?' checked="checked"':null;
            }
            $parent_checked = empty($checked)?'':$parent_checked;
        	$roles.= '<label><input type="checkbox" name="roles[]" rel="'.$k.'" value="'.$sk.'"'.$checked.' /> '.$spv.'</label>';
        }
        $hl.= '<p><label><input type="checkbox" name="parent[]" class="parent-'.$k.'" value="'.$k.'"'.$parent_checked.' /> <strong>'.$title.'</strong></label><br/>'.$roles.'</p>';
    }
    $hl.= '</div>';
    return $hl;
}
// 系统权限
$LC_Purview = array(
    'users' => array(
        '_LABEL_'           => '用户管理',
        'user-list'         => '用户列表',
        'user-new'          => '添加用户',
        'user-edit'         => '编辑用户',
        'user-delete'       => '删除用户',
    ),
    /*'plugins' => array(
        '_LABEL_'           => 'Plugins'),
        'plugin-list'       => _x('List','plugin'),
        'plugin-new'        => _x('Add New','plugin'),
        'plugin-delete'     => _x('Delete','plugin'),
    ),
    'settings' => array(
        '_LABEL_'           => 'Settings',
        'option-general'    => 'General',
        'option-posts'      => 'Posts',
    )*/
);
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
	    // 权限检查
	    current_user_can('user-new');
	    // 重置标题
	    system_head('title','添加用户');
	    include 'header.php';
        // 显示页面
	    user_manage_page('add');
        include 'footer.php';
	    break;
	// 活塞式运动，你懂得。。。
	case 'edit':
	    // 所属
        $parent_file = 'user.php';
	    // 权限检查
	    current_user_can('user-edit');
	    // 重置标题
	    system_head('title','Edit User');
	    // 添加JS事件
	    //system_head('loadevents','user_manage_init');
	    include 'header.php';
	    user_manage_page('edit');
        include 'footer.php';
	    break;
	// 保存用户
	case 'save':
	    $userid = isset($_POST['userid'])?$_POST['userid']:null;
	    current_user_can($userid?'user-edit':'user-new');
	    
        if (validate_is_post()) {
            $username  = isset($_POST['username'])?$_POST['username']:null;
            $password  = isset($_POST['password1'])?$_POST['password1']:null;
            $password2 = isset($_POST['password2'])?$_POST['password2']:null;
            $nickname  = isset($_POST['nickname'])?$_POST['nickname']:null;
            $email     = isset($_POST['email'])?$_POST['email']:null;
            $url       = isset($_POST['url'])?$_POST['url']:null;
            $desc      = isset($_POST['description'])?$_POST['description']:null;
            $bcpwd     = isset($_POST['BanChangePassword'])?$_POST['BanChangePassword']:null;
            $mplogin   = isset($_POST['MultiPersonLogin'])?$_POST['MultiPersonLogin']:'Yes';
            $roldes    = isset($_POST['roles'])?$_POST['roles']:array();
			
			$isadmin   = isset($_POST['Administrator'])?$_POST['Administrator']:null;
			if($isadmin=='ALL')
				$roldes = 'ALL';
			
            if ($userid) {
            	$user = user_get_byid($userid); $is_exist = true;
            	if ($username != $user['username']) {
            		$is_exist = user_get_byname($username)?false:true;
            	}
                //if ($user['roles']=='ALL') $roldes = 'ALL';
            	unset($user);
            } else {
                $is_exist = user_get_byname($username)?false:true;
            }
			
            // 验证用户名
            validate_check(array(
                // 用户名不能为空
                array('username',VALIDATE_EMPTY,'The username field is empty.'),
                // 用户名长度必须是2-30个字符
                array('username',VALIDATE_LENGTH,'The username field length must be %d-%d characters.',2,30),
                // 用户已存在
                array('username',$is_exist,'The username already exists.'),
            ));
            // 验证email
            validate_check(array(
                array('email',VALIDATE_EMPTY,'Please enter an e-mail address.'),
                array('email',VALIDATE_IS_EMAIL,'You must provide an e-mail address.')
            ));
            // 验证密码
            if ((!$userid) || $password) {
                validate_check(array(
                    array('password1',VALIDATE_EMPTY,'Please enter your password.'),
                    array('password2',VALIDATE_EMPTY,'Please enter your password twice.'),
                    array('password1',VALIDATE_EQUAL,'Your passwords do not match. Please try again.','password2'),
                ));
            }
            // 验证通过
            if (validate_is_ok()) {
                $username = esc_html($username);
                $email    = esc_html($email);
                $user_info = array(
                    'url'  => esc_html($url),
                    'roles' => $roldes,
                    'nickname' => esc_html($nickname),
                    'Administrator' => 'Yes',
                    'BanChangePassword' => $bcpwd,
                    'MultiPersonLogin'  => $mplogin,
                );
                // 编辑
                if ($userid) {
                    $user_info = array_merge($user_info,array(
                        'username'    => $username,
                        'description' => esc_html($desc)
                    ));
                    // 修改暗号
                    if ($password) {
                    	$user_info = array_merge($user_info,array(
                    	   'password' => md5($password),'authcode' => '',
                    	));
                    }
                    user_edit($userid,$user_info);
					//echo 'User updated.';
                    //alert_echo('User updated.'.PHP_FILE);
					redirect(PHP_FILE);
                } 
                // 强力插入
                else {
                    user_add($username,$password,$email,$user_info);
					 //alert_echo( 'User created.');
					 redirect(PHP_FILE);
                }
            }
        }
	    break;
	// 批量动作
	case 'bulk': 
	    $action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	alert_echo( 'Did not select any item.');
	    }
	    switch ($action) {
	        case 'delete': 
	            current_user_can('user-delete');
	            if(user_delete($listids)){	           
	            echo '删除成功'; //redirect('user.php');
				}else{
					echo '删除失败';
				}
	            break;
            default:
				echo 'Parameter is invalid.'; //redirect('user.php');
                break;
	    }
	    break;
	default:
	    current_user_can('user-list');
		//system_head('title','Add New User');
	    system_head('title','用户管理');
        //$result = pages_query("SELECT `admin_name` FROM `admin`");
		$result = pages_query("SELECT `uid` FROM `#@_user` ");
			
        include 'header.php';
        echo '<div class="wrap">';
        echo   '<h2>用户管理<a class="button" href="user.php?method=new">添加新用户</a></h2>';
        echo   '<form action="user.php?method=bulk" method="post" name="userlist" id="userlist">';		
        table_nav();
        echo       '<table class="data-table fixed" cellspacing="0">';
        echo           '<thead>';		
        table_thead();
        echo           '</thead>';
        echo           '<tfoot>';
        table_thead();
        echo           '</tfoot>';
        echo           '<tbody class="table-body">';
		
        while ($data = pages_fetch($result)) {
            $user = user_get_byid($data['uid']);
			//print_r($user);
            if ($user['uid']==$_USER['uid']) {
            	$href = 'profile.php?referer=user.php';
            	$actions = '<span class="edit"><a href="'.$href.'">编辑</a></span>';
            } else {
                $href = 'user.php?method=edit&userid='.$user['uid'];
                $actions = '<span class="edit"><a href="'.$href.'">编辑</a> | </span>';
                $actions.= '<span class="delete"><a href="javascript:;" onclick="user_delete('.$user['uid'].')">删除</a></span>';
            }
            echo           '<tr>';
            echo               '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$user['nickname'].'" /></td>';
            echo               '<td><strong><a href="'.$href.'">'.$user['nickname'].'</a></strong><br/><div class="row-actions">'.$actions.'</div></td>';
			echo               '<td><strong title="你登陆时显示的名字">'.$user['nickname'].'</strong></td>';
            echo               '<td>'.$user['email'].'</td>';
            echo               '<td>'.get_icon('c'.($user['status']+3)).'</td>';
            echo               '<td>'.$user['registered'].'</td>';
            echo           '</tr>';
        }
        echo           '</tbody>';
        echo       '</table>';
        table_nav();
        echo   '</form>';
        echo '</div>';
        include 'footer.php';
        break;
}

/**
 * 批量操作
 *
 */
function table_nav() {
    // 分页地址
    $page_url = 'user.php?'.http_build_query(array(
        'page' => '$',
    ));
    echo '<div class="table-nav">';
    echo     '<select name="actions">';
    echo         '<option value="">批量操作</option>';
    echo         '<option value="delete">删除</option>';
    echo     '</select>';
    echo     '<button type="button" class="button">应用</button>';
    echo     pages_list($page_url);
    echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
    echo '<tr>';
    echo     '<th class="check-column"><input type="checkbox" name="select" value="all" /></th>';
    echo     '<th>用户名</th>';
	echo     '<th>昵称</th>';
    echo     '<th>邮箱</th>';
    echo     '<th>状态</th>';
    echo     '<th>注册日期</th>';
    echo '</tr>';
}


/**
 * 用户管理页面
 *
 * @param string $action
 */
function user_manage_page($action) {
    $referer = referer(PHP_FILE);
    $userid  = isset($_GET['userid'])?$_GET['userid']:0;
    if ($action!='add') {
    	$_USER  = user_get_byid($userid);
    }
    $username = isset($_USER['username'])?$_USER['username']:null;
    $nickname = isset($_USER['nickname'])?$_USER['nickname']:null;
    $email    = isset($_USER['email'])?$_USER['email']:null;
    $url      = isset($_USER['url'])?$_USER['url']:null;
    $desc     = isset($_USER['description'])?$_USER['description']:null;
    $bcpwd    = isset($_USER['BanChangePassword'])?$_USER['BanChangePassword']:null;
    $mplogin  = isset($_USER['MultiPersonLogin'])?$_USER['MultiPersonLogin']:'No';
    $roles    = isset($_USER['roles'])?$_USER['roles']:null;
	
	
	//print_r($roles);
    echo '<div class="wrap">';
    echo   '<h2>'.system_head('title').'</h2>';
    echo   '<form action="user.php?method=save" method="post" name="usermanage" id="usermanage">';
    echo     '<fieldset>';
    echo       '<table class="form-table">';
    echo           '<tr>';
    echo               '<th><label for="username">用户名<span class="resume">(必填)</span></label></th>';
    echo               '<td><input class="text" id="username" name="username" type="text" size="20" value="'.$username.'" /></td>';
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
    if ($action == 'add') {
        echo       '<tr>';
        echo           '<th><label for="password1">密码<span class="resume">(两次,必填)</span></label></th>';
        echo           '<td><input class="text" id="password1" name="password1" type="password" size="20" /><br/><input class="text" id="password2" name="password2" type="password" size="20" />';
        echo           '<br /><div id="pass-strength-result" class="pass-strength">强度</div></td>';
        echo       '</tr>';
    } else {
        echo       '<tr>';
        echo           '<th><label for="description">Biographical Info</label></th>';
        echo           '<td><textarea class="text" cols="70" rows="5" id="description" name="description">'.$desc.'</textarea>';
        echo               '<br/><span class="resume">分享一些你的个人简介，这可能会公开显示。</span>';
        echo           '</td>';
        echo       '</tr>';
        echo       '<tr>';
        echo           '<th><label for="password1">New Password<span class="resume">(twice)</span></label></th>';
        echo           '<td><input class="text" id="password1" name="password1" type="password" size="20" />';
        echo               ' <span class="resume">如果你想更改密码，请填写一个新的密码，否则留空。</span>';
        echo               '<br/><input class="text" id="password2" name="password2" type="password" size="20" /> <span class="resume">再次输入你的新密码。</span>';
        echo               '<br /><div id="pass-strength-result" class="pass-strength">密码强度指示器</div>';
        echo           '</td>';
        echo       '</tr>';
    }
	echo           '<tr>';
    echo               '<th><label>权限</label></th>';
    echo               '<td>';
	
	echo                    '<div class="role-list">';
    echo                        '<p><label for="Administrator"><input type="checkbox" name="Administrator" id="Administrator" value="ALL"'.($roles=='ALL'?' checked="checked"':null).' /><span style="color:red;font-weight:bold">超级管理员</span></label></p>';
    echo                    '</div>';
	
    echo                    '<div class="role-list">';
    echo                        '<label for="BanChangePassword"><input type="checkbox" name="BanChangePassword" id="BanChangePassword" value="Yes"'.($bcpwd=='Yes'?' checked="checked"':null).' />禁止修改密码</label>';
    echo                        '<label for="MultiPersonLogin"><input type="checkbox" name="MultiPersonLogin" id="MultiPersonLogin" value="No"'.($mplogin=='No'?' checked="checked"':null).' />禁止多人同时登陆</label>';
    echo                    '</div>';
	//echo                    system_purview($roles);
	echo                    '<div class="role-list">';
    echo '<p><label><input type="checkbox" value="posts" class="parent-posts" name="parent[]"> <strong>留言管理</strong></label><br><label><input type="checkbox" value="inquiry-view" class="test1" name="roles[]"'.(instr("inquiry-view",(array)$roles)?' checked="checked"':null).'> 查看留言</label><label><input type="checkbox" value="inquiry-mark" rel="posts" name="roles[]" '.(instr("inquiry-mark",(array)$roles)?' checked="checked"':null).' /> 标记留言状态</label><label><input type="checkbox" value="inquiry-delete" rel="posts" name="roles[]"'.(instr("inquiry-delete",(array)$roles)?' checked="checked"':null).'> 删除留言</label></p>';
    echo                    '</div>';
	
    echo                    '<button type="button" rel="select" class="button">全选</button>';
    echo               '</td>';
    echo           '</tr>';
	
    echo       '</table>';
    echo   '</fieldset>';
    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="button">添加用户</button>';
    } else {
        echo   '<button type="submit" class="button">更新用户</button><input type="hidden" name="userid" value="'.$userid.'" />';
    }
    echo       '<button type="button" class="button" onclick="location.replace(\''.$referer.'\')">返回</button>';
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}