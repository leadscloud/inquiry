<?php
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
header('Content-Type: text/html; charset=UTF-8');

$_USER = user_current();

system_head('title','控制面板');
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
include 'header.php';
$db = get_conn();
        echo '<div class="wrap">';
        echo    '<h2>控制面板</h2>';
		echo '<div class="clear"></div>';
        echo    '<div class="container">';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="点击切换"><br/></a>';
        echo            '<h3>概况</h3>';
        echo            '<div class="inside right-now">';
        echo                '<div class="content">';
        echo                '<h4>留言</h4>';
        echo                '<table cellspacing="0">';
        echo                    '<tr><td class="number"><a href="inquiry.php?type=inquiry">'.post_count('').'</a></td><td><a href="inquiry.php?type=inquiry">留言</a></td></tr>';
        echo                    '<tr><td class="number"><a href="inquiry.php?read=1">'.post_count('read').'</a></td><td><a href="inquiry.php?read=1">已读</a></td></tr>';
        echo                    '<tr><td class="number"><a href="inquiry.php?read=0">'.post_count('noread').'</a></td><td><a href="inquiry.php?read=0">未读</a></td></tr>';
        echo                '</table>';
        echo                '</div>';
        echo                '<div class="discussion">';
        echo                '<h4>用户信息</h4>';
        echo                '<table cellspacing="0">';
        echo                    '<tr><td>账号： '.$_USER['username'].'</td></tr>';
        echo                    '<tr><td>显示名称： '.$_USER['nickname'].'</td></tr>';
		echo                    '<tr><td>你的邮箱： '.$_USER['email'].'</td></tr>';
        echo                '</table>';
        echo                '</div>';
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="点击切换"><br/></a>';
        echo            '<h3>服务器环境</h3>';
        echo            '<div class="inside server-env">';
        echo                '<p><label>服务器系统：</label>'.PHP_OS .' '. php_uname('r') .' On '. php_uname('m').'</p>';
        echo                '<p><label>服务器软件：</label>'.$_SERVER['SERVER_SOFTWARE'].'</p>';
        echo                '<p><label>服务器 API： </label>'.PHP_SAPI.'</p>';
        echo                '<p><label>系统版本：</label><span class="version">'.SYS_VERSION.'</span></p>';
        echo                '<p><label>PHP版本：</label>'.PHP_VERSION.'&nbsp; '.test_result(version_compare(PHP_VERSION,'4.3.3','>')).'</p>';
        echo                '<p><label>SQLITE版本： </label>'.$db->getServerVersion().'&nbsp; '.test_result(version_compare($db->getServerVersion(),'3.7.7','>')).'</p>';
        echo                '<p><label>图形绘制库： </label>'.(function_exists('gd_info') ? GD_VERSION : __('Not Supported')).'&nbsp; '.test_result(function_exists('gd_info')).'</p>';
        echo                '<p><label>字符集转换：</label>'.(function_exists('iconv') ? ICONV_VERSION : __('Not Supported')).'&nbsp; '.test_result(function_exists('iconv')).'</p>';
        echo                '<p><label>多字节字符串：</label>'.(extension_loaded('mbstring') ? 'mbstring' : __('Not Supported')).'&nbsp; '.test_result(extension_loaded('mbstring')).'</p>';
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<fieldset>';
        echo            '<a href="javascript:;" class="toggle" title="点击切换"><br/></a>';
        echo            '<h3>关于本系统</h3>';
        echo            '<div class="inside lazy-team">';
        echo                '<p><label>作者： </label><a href="https://sbmzhcn.github.io" target="_blank">Ray</a> <a href="mailto:sbmzhcn@gmail.com">&lt;sbmzhcn@gmail.com&gt;</a></p>';
        echo                '<p><label>博客：</label><a href="https://sbmzhcn.github.io/" target="_blank">https://sbmzhcn.github.io</a></p>';
        echo                '<p><label>QQ：</label> 75504026</p>';
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<div class="clear"><br/></div>';
        echo    '</div>';
        echo    '<div class="container">';
        echo        '<fieldset cookie="true">';
        echo            '<a href="javascript:;" class="toggle" title="Click to toggle"><br/></a>';
        echo            '<h3>最新留言</h3>';
        echo            '<div class="inside comments">';
		
		$sql='select * from `#@_inquiry` where `type`="inquiry" order by `time` desc;';      
        $result = pages_query($sql);
		 
		if ($result) { 
			$i = 0;
        	while ($data = pages_fetch($result)) {  
				if($i>4){
					break;
				}           	
            	$i++;
        
        		if ($i==0) {
            	echo        '<div class="empty">No comments yet.</div>';
        		} else { 
					$post = post_get($data['id']);    
					      
					$actions= '<span class="reply"><a href="inquiry.php?method=view&postid='.$data['id'].'">查看</a> | </span>';
				if(current_user_can('inquiry-delete',false)){
					$actions.= '<span class="delete"><a href="javascript:;" onclick="deleteData('.$data['id'].');" title="立即删除">删除</a> </span>';
				}
            
            //$actions.= '<span class="delete"><a href="javascript:;" onclick="comment_delete('.$data['cmtid'].')">删除</a></span>';
            		echo            '<div class="comment">';
            		echo                '<div class="comment-wrap">';
            		echo                    '<span class="author">'.sprintf('From %s', '<cite>'.$post['name'].'</cite>').'</span>';
					echo                    '<span class="author">'.sprintf(' | %s', '<cite>'.$post['email'].'</cite>').'</span>';
					echo					'<span class="author"> '.ip2addr($post['ip']).'</span>';
            		echo                    '<div class="content">  '.$post['content'].'</div>';
            		echo                    '<div class="row-actions">'.$actions.'</div>';
            		echo                '</div>';
            		echo                '<div class="clear"><br/></div>';
            		echo            '</div>';           	
        		}
			}
			echo  '<div class="buttons"><a href="inquiry.php" class="button">查看所有</a></div>';
		}
        echo            '</div>';
        echo        '</fieldset>';
        echo        '<div class="clear"><br/></div>';
        echo    '</div>';
        echo '</div>';
        // 加载尾部
        include 'footer.php';
?>