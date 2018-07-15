<?php
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
error_reporting(1);
// 文件名
$php_file = isset($php_file) ? $php_file : PHP_FILE;


$_USER = user_current();

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

// 方法
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

$message ='';

trashed_message_auto_clear();

switch ($method) {
	case 'delete':
		// 权限检查
	    current_user_can('inquiry-delete'); //更多 inquiry-view inquiry-mark
		$referer = referer(PHP_FILE);
		$delID = isset($_GET['postid'])?$_GET['postid']:null;
		$newxtid = isset($_GET['nextid'])?$_GET['nextid']:null;
		post_delete($delID);
		if($newxtid!=null)
			redirect('inquiry.php?method=view&postid='.$newxtid);
		else
			redirect('inquiry.php');
		//echo $referer;
		//header("Location: $referer");
		//echo $crusher;
		break;
	
	case 'trash':
		//echo '213';
	    $action  = isset($_REQUEST['action'])?$_REQUEST['action']:null;
	    $listids = isset($_GET['listids'])?$_GET['listids']:null;
	    if (empty($listids)) {
	    	echo 'Did not select any item.' ;
	    }
		foreach ($listids as $postid) {
	        
			foreach ($listids as $postid) {
	            	post_delete($postid);
	        }
	    }
		
	    break;
	// 查看
	case 'view':
	
		
		require_once('translate/config.inc.php');
		require_once('translate/class/ServicesJSON.class.php');
		require_once('translate/class/MicrosoftTranslator.class.php');


		$translator = new MicrosoftTranslator(ACCOUNT_KEY);
		//$selectbox = array('id'=> 'txtLang','name'=>'txtLang');
		//$translator->getLanguagesSelectBox($selectbox);
		//echo 'test '.$translator->testGet();

		$referer = referer(PHP_FILE,false);
		
		$page = isset($_GET['page'])?$_GET['page']:0;
		$read = isset($_GET['read'])?$_GET['read']:null;
		
		//echo $query_url;
		
		$query_url = array('page' => $page,'read' => $read);
		$query_url = http_build_query($query_url);

		
		system_head('title','详细信息');
		$postid = isset($_GET['postid'])?$_GET['postid']:0;
		include 'header.php';
		$data  = post_get($postid);
		$fromip = $data['ip']!=''?$data['ip']:'Unknow';
		$country = $data['country']!=''?$data['country']:'Unknow';
		
		
		$title    = $data['title'];

		//meta field
		$useplace = isset($data['meta']['userplace'])?$data['meta']['userplace']:null;
		$products = isset($data['meta']['products'])?$data['meta']['products']:null;
		$materials = isset($data['meta']['materials'])?$data['meta']['materials']:null;
		$application = isset($data['meta']['application'])?$data['meta']['application']:null;
		$capacity = isset($data['meta']['capacity'])?$data['meta']['capacity']:null;
		$imtype = isset($data['meta']['imtype'])?$data['meta']['imtype']:null;
		$imvalue = isset($data['meta']['imvalue'])?$data['meta']['imvalue']:null;

		
		//把内容处理成段落格式
		$inquiry_content = '';
		$newarr = explode("\n",$data['content']);
		//print_r($newarr);
      	foreach($newarr as $str) {
			if(trim($str)!=="")
				$inquiry_content .= "<p>".$str."</p>";
		}
		
		$page = pages_info();
		$db = get_conn();
		$presql = 'SELECT `id` FROM `#@_inquiry` WHERE id > '.$postid.' AND `type`="inquiry" ORDER BY id ASC LIMIT 1';
		$nextsql = 'SELECT `id` FROM `#@_inquiry` WHERE id < '.$postid.' AND `type`="inquiry" ORDER BY id DESC LIMIT 1';
		
		$prepostid = $db->get_results($presql);
		$nextpostid = $db->get_results($nextsql);

		// 标记未读状态
		if(current_user_can('inquiry-mark',false)&&$postid){
			$datas['read']    = 1;
			post_updata($postid,$datas);
		}
		
		/*
		$parsed_url = parse_url($referer);
		$querystr = $parsed_url['query'];
		$tempquerystr = $querystr;
		//echo $querystr;
		if (!empty($querystr)) {
        	parse_str($querystr,$query);
        	if (!isset($query['method'])) {
            	$query = array_merge(array('page' => '$'),$query);
				$query = '?'.http_build_query($query);
        	}
        	// 
    	}
		echo $query;
		*/
		
		
		echo '<script src="translate/js/json-jquery.js" type="text/javascript"></script>';
		echo '<div class="wrap">';
		if(current_user_can('inquiry-delete',false)){
			echo   '<h2 id="d_clip_container">详细信息<a class="button" href="'.preg_replace('/\s\s+/',null,$referer.'?'.$query_url).'">返回</a> <a class="button" href="inquiry.php?postid='.$data['id'].'&method=delete&nextid='.$nextpostid['id'].'">删除</a> <a class="button btn-copy" id="d_clip_button" href="javascript:;">一键复制</a></h2>';
		}else{
			echo   '<h2>详细信息<a class="button" href="'.$referer.'">返回</a> </h2>';
		}
		
		echo '<div class="nextpost_top">';
		echo '<span>共有（<b>'.post_count('ALL').'</b>）个</span><span>已读（<b>'.post_count('read').'</b>）个</span><span>未读（<b>'.post_count('noread').'</b>）个</span>';
		if($prepostid)
			echo '<a href="inquiry.php?method=view&postid='.$prepostid['id'].'&'.$query_url.'">上一条</a>';
		if($nextpostid)
			echo '<a href="inquiry.php?method=view&postid='.$nextpostid['id'].'&'.$query_url.'">下一条</a>';
		echo '</div>';	
		echo '<div class="clear"></div>';
		echo '<div id="message" class="updated below-h2 hide"></div>';
		echo '<div id="inquiryDetails" style="display:none"><p>来源IP:'.$fromip.'</p><p>国家:'.$country.' &nbsp; '.ip2addr($fromip).'</p><p>来源网站:'.$data['website'].'</p><p>标题:'.$title.'</p><p>姓名:'.$data['name'].'</p><p>邮箱:'.$data['email'].'</p><p>内容:'.$inquiry_content.'</p><p>电话:'.$data['phone'].'</p><p>地址:'.$data['address'].'</p></div>';
		echo   '<form action="inquiry.php?method=delete&postid="'.$data['id'].' method="post" name="postmanage" id="postmanage">';
		echo   '<fieldset'.($data['from_company']=='SB'?' class=\"from_sb\"':' class="from_company"').'>';
		echo       '<table class="form-table">';
		echo			'<thead class="infos">';
		echo               '<tr>';
		//echo                   '<td><label>一般信息 </td>';
		echo                   '<td colspan="2"><span>来源 IP: <a target="_blank" title="点击查看详细信息" href="http://www.123cha.com/ip/?q='.$fromip.'">'.$fromip.'</a> (检测结果：'.ip2addr($fromip).'  )</span><span>所属国家:'.$country.'</span><span>时间:'.$data['time'].'</span><span>来源网站:'.get_true_refer($data['website']).'</span></td>';
		echo               '</tr>';
		echo			'</thead>';
		echo           '<tbody>';
			echo           '<tr class="taxonomyid">';
			echo               '<th>标题 <span>(Title)</span></th>';
			echo               '<td>';
			echo                   $title;
			echo               '</td>';
			echo           '</tr>';
		echo               '<tr>';
		echo                   '<th>姓名 <span>(Name)</span></th>';
		echo                   '<td>';
		echo                       $data['name'];
		echo                   '</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>邮件 <span>(Email)</span></th>';
		echo                   '<td>'.$data['email'].'</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>即时通信'.$imtype.'</th>';
		echo                   '<td>'.$imtype.' : '.$imvalue.'</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>内容 <span>(Contents)</span></th>';
		echo                   '<td id="inquiryContent"><div id="txtString">'.$inquiry_content.'</div></td>';
		//$tran = Google_translate_API();
		echo               '</tr>';
		echo               '<tr>';
		echo 					'<th><select id ="txtLang" name="txtLang"><option value="zh-CHS">zh-CHS</option><option value="zh-CHT">zh-CHT</option><option value="en">英语</option></select><a class="black pl10" href="#" id="getdata-button">翻译</a></th>';
		//echo                   '<th><label>翻译 '.$translator->response->languageSelectBox.'<a class="black pl10" href="#" id="getdata-button">Translate</a></th>';
		//echo $translator->translate('en','fr','crusher','raw');
		//print_r( $translator->response);
		echo                   '<td id="TranslateResult"><div class="bgwhite width500" id="showdata"></div><div class="bgwhite width500" id="showendata"></div></td>';
		echo 					'';
		echo               '</tr>';
		echo               '<tr>';
  //echo $res;
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>电话 <span>(Phone)</span></th>';
		echo                   '<td id="phone_num">'.$data['phone'].'</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>地址 <span>(Address)</span></th>';
		echo                   '<td>'.$data['address'].'</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>来源 <span>(From)</span></th>';
		echo                   '<td><a href="'.$data['website'].'" target="_blank" title="新窗口打开网站">'.get_true_refer($data['website']).'</a></td>';
		echo               '</tr>';

		echo               '<tr>';
		echo                   '<th>浏览器及系统</th>';
		echo                   '<td>' . $data['browser_name'] . " " . $data['browser_version']  .$data['browser_platform'] . " <br >" . $data['user_agent'].'</td>';
		echo               '</tr>';
		echo               '<tr>';
		echo                   '<th>浏览器语言</th>';
		echo                   '<td>' . $data['lang'] .'</td>';
		echo               '</tr>';




		if(isset($data['meta'])) {
			echo               '<tr>';
			echo                   '<th>所要产品 <span>Pproducts)</span></th>';
			echo                   '<td>'.$products.'</td>';
			echo               '</tr>';
			echo               '<tr>';
			echo                   '<th>物料 <span>(Materials)</span></th>';
			echo                   '<td>'.$materials.'</td>';
			echo               '</tr>';
			echo               '<tr>';
			echo                   '<th>应用领域 <span>(Application)</span></th>';
			echo                   '<td>'.$application.'</td>';
			echo               '</tr>';
			echo               '<tr>';
			echo                   '<th>产量 <span>(Capacity)</span></th>';
			echo                   '<td>'.$capacity.'</td>';
			echo               '</tr>';
			echo               '<tr>';
			echo                   '<th>使用地 <span>(Use Place)</span></th>';
			echo                   '<td>'.$useplace.'</td>';
			echo               '</tr>';
		}
		echo               '<!--<tr>';
		echo                   '<th>网站所属品牌</th>';
		echo                   '<td>'.($data['from_company']==NULL?"西芝":$data['from_company']).'</td>';
		echo               '</tr>-->';
		echo               '<tr>';
		echo                   '<th><div id="d_clip_container2" style="position:relative">
   <div id="d_clip_button2" class="button-secondary btn-copy">一键复制</div>
  </div></th>';
		echo                   '<td></td>';
		echo               '</tr>';
		echo           '</tbody>';
		echo       '</table>';
		echo   '</fieldset>';
		//echo   '<p class="submit">';
		echo  '</form>';
		echo '</div>';
		include 'footer.php';       
		break;
			
    default:
		$post_per_page = isset($_USER['post_per_page'])?$_USER['post_per_page']:25;
		pages_init($post_per_page,null);
		$action  = isset($_REQUEST['action'])?$_REQUEST['action']:null;
		//$action  = isset($_POST['action'])?$_POST['action']:null;
		$listids = isset($_GET['listids'])?$_GET['listids']:null;
		$read   = isset($_REQUEST['read'])?$_REQUEST['read']:'';
		
		$inquiry_type   = isset($_GET['type'])?$_GET['type']:null;
	    
		
		$trashed = isset($_REQUEST['trashed'])?$_REQUEST['trashed']:'';
		$untrashed = isset($_REQUEST['untrashed'])?$_REQUEST['untrashed']:'';
		$mark = isset($_REQUEST['mark'])?$_REQUEST['mark']:'';
		
		$page = pages_info();
		
		if($read!=''){ //echo '123';
			if($read=="1"){
				$where = "WHERE `read`=1 AND `type`='inquiry'";
			}else{
				$where = "WHERE `read`=0 AND `type`='inquiry'";
			}
		}else{
			$where	= "WHERE `type`='inquiry'";
		}
		
		if($inquiry_type=='trash'){
			$where = "WHERE `type`='trashed'";
		}
		
		if($action=='trash'){
			$ID = isset($_GET['postid'])?$_GET['postid']:null;
			if(!empty($ID))
				$listids = preg_split('/[\s,]+/',$ID);
				
			if (empty($listids)) {
	    		$message= '没有选择任何项目。' ;
	    	}else{
				current_user_can('post-delete');
				foreach ($listids as $postid) {
					post_trashed($postid);
					$message= '项目已经移到垃圾箱。<a href="?read='.$read.'&page='.$page['page'].'&action=untrash&postid='.implode(",",$listids).'">撤销</a>' ;
				}
			}
		}elseif($action=='untrash'){
			$ID = isset($_GET['postid'])?$_GET['postid']:null;
			if(!empty($ID))
				$listids = preg_split('/[\s,]+/',$ID);
				
			if (empty($listids)) {
	    		$message= '没有选择任何项目。' ;
	    	}else{
				current_user_can('inquiry-delete');
				foreach ($listids as $postid) {
					post_trashed($postid,'untrashed');
					$message= '项目从垃圾箱中恢复。<a href="?read='.$read.'&page='.$page['page'].'&action=trash&postid='.implode(",",$listids).'">撤销</a>' ;
				}
			}
		}elseif($action=='delete'){
			if (empty($listids)) {
	    		$message= '没有选择任何项目。' ;
	    	}else{
				current_user_can('inquiry-delete');
				foreach ($listids as $postid) {
					post_delete($postid);
					$message= '项目已经被彻底删除。' ;
				}
			}
		}
		
		if($action=='mark'){
			current_user_can('mark');
			$ID = isset($_GET['postid'])?$_GET['postid']:null;
			if(!empty($ID))
				$listids = preg_split('/[\s,]+/',$ID);
				
			if (empty($listids)) {
	    		$message= '没有选择任何项目。' ;
	    	}else{
				//current_user_can('mark');
				foreach ($listids as $postid) {
					post_mark($postid,'noread');
					$message= '项目已经标记为未读。<a href="?read='.$read.'&page='.$page['page'].'&action=unmark&postid='.implode(",",$listids).'">撤销</a>' ;
					//header("Location: ".PHP_FILE."?page=".$page['page']);
				}
			}
		}elseif($action=='unmark'){
			current_user_can('mark');
			$ID = isset($_GET['postid'])?$_GET['postid']:null;
			if(!empty($ID))
				$listids = preg_split('/[\s,]+/',$ID);
				
			if (empty($listids)) {
	    		$message= '没有选择任何项目。' ;
	    	}else{
				foreach ($listids as $postid) {
					post_mark($postid,'read');
					$message= '项目已经标记为已读。<a href="?read='.$read.'&page='.$page['page'].'&action=mark&postid='.implode(",",$listids).'">撤销</a>' ;
				}
			}
			
		}
		$pagenum = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		//$delete_all  = isset($_REQUEST['delete_all'])?$_REQUEST['delete_all']:null;
		if ( isset( $_REQUEST['delete_all'] ) ){
			$redirect_to = remove_query_arg( array( 'action', 'delete_all', 'read', 'method', 'postid' ), get_referer() );
			$redirect_to = add_query_arg( 'page', $pagenum, $redirect_to );
			$db = get_conn();
			$db->exec('DELETE FROM #@_inquiry WHERE type="trashed"');
			redirect($redirect_to);
			//echo $redirect_to;
			//echo 'true';
		}
		
		
		
	//header('Content-Type: text/html; charset=UTF-8');
            //current_user_can('post-list');
			
		system_head('title','询盘管理');
		
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$datetime   = isset($_REQUEST['datetime'])?$_REQUEST['datetime']:null;
		$end_datetime   = isset($_REQUEST['end_datetime'])?$_REQUEST['end_datetime']:null;
		//$query    = array('page' => '$');
		
		if ($search) {
			if($read=="yes"){
				$where = "WHERE `content` LIKE '%%$search%%' AND `read`=1";
			}elseif($read=="no"){
				$where = "WHERE `content` LIKE '%%$search%%' AND `read`=0";
			}else{
				$where = "WHERE `content` LIKE '%%$search%%'";
			}
		}
		
		
		
		if ($datetime||$end_datetime) {
			$year=((int)substr($datetime,0,4));//取得年份
			$month=((int)substr($datetime,5,2));//取得月份
			$day=((int)substr($datetime,8,2));//取得几号
			$datetime = mktime(0,0,0,$month,$day,$year);
			$end_datetime = mktime(0,0,0,((int)substr($end_datetime,5,2)),((int)substr($end_datetime,8,2)),((int)substr($end_datetime,0,4)));
		
			$where = "WHERE `time` > '".date('Y-m-d H:i:s',$datetime)."' AND `time` < '".date('Y-m-d H:i:s',$end_datetime)."' AND `type` = 'inquiry'";
		}
		//echo $where;
		
		//header('Content-Type: text/html; charset=UTF-8');
		
		if($datetime)
			$sql="select * from `#@_inquiry` {$where} order by `time` DESC";
		else
			$sql="select * from `#@_inquiry` {$where} order by `time` DESC";
		//echo $sql;
		$query    = array('page' => '$');
		
        $result = pages_query($sql);
		$page = pages_info();
		//print_r($page);
		
        // 分页地址
		$inquiry_type = isset($_GET['type'])?$_GET['type']:null;
		$url_params = array();
		if (isset($read)) {
			$query['read'] = $read;
		}
		$query['datetime'] = isset($datetime)?date('Y-m-d',$datetime):null;
		$query['end_datetime'] = isset($end_datetime)?date('Y-m-d',$end_datetime):null;
		$query['type'] = isset($_GET['type'])?$_GET['type']:null;

/*
		if($read=='0'||$read=='1') 
			$page_url = PHP_FILE.'?'.http_build_query(array(
				'type' => $inquiry_type,
        		'read' => $read,
				'datetime'=>date('Y-m-d',$datetime),
				'end_datetime'=>date('Y-m-d',$end_datetime),
        		'page'   => '$',
    		));
		elseif($inquiry_type!=null&&$datetime!=null)
        	$page_url   = PHP_FILE.'?'.http_build_query(array(
        		'type' => $inquiry_type,
				'datetime'=>date('Y-m-d',$datetime),
				'end_datetime'=>date('Y-m-d',$end_datetime),
        		'page'   => '$',
    		));
		elseif($datetime!=null&&$end_datetime!=null)
			$page_url   = PHP_FILE.'?'.http_build_query(array(
        		'type' => $inquiry_type,
				'datetime'=>date('Y-m-d',$datetime),
				'end_datetime'=>date('Y-m-d',$end_datetime),
        		'page'   => '$',
    		));
		else
			$page_url   = PHP_FILE.'?'.http_build_query($query);
*/		

		$page_url   = PHP_FILE.'?'.http_build_query($query);
		// echo $page_url ;

		//$referer = referer($page_url);
		//echo $referer;
		
        include 'header.php';
        echo '<div class="wrap">';
        echo   '<h2>询盘管理</h2>';
		echo   '<div class="clear"></div>';
		if($message)
		echo   '<div id="message" class="updated below-h2"><p>'.$message.'</p></div>';
		echo   '<ul class="subsubsub">';
		echo   '<li class="all"><a href="inquiry.php?type=inquiry" '.($inquiry_type=='inquiry'?'class="current"':'').'>全部 <span class="count">('.post_count('ALL').')</span></a> |</li>';
		echo   '<li class="publish"><a href="inquiry.php?read=1" '.($read=='1'?'class="current"':'').'>已读 <span class="count">('.post_count('read').')</span></a> |</li>';
		echo   '<li class="publish"><a href="inquiry.php?read=0" '.($read=='0'?'class="current"':'').'>未读 <span class="count">('.post_count('noread').')</span></a> |</li>';
		echo   '<li class="publish"><a href="inquiry.php?type=trash" '.($inquiry_type=='trash'?'class="current"':'').'>垃圾箱 <span class="count">('.post_count('trash').')</span></a></li>';
		
		echo   '</ul>';
        echo   '<form header="POST '.PHP_FILE.'?method=bulk" action="'.PHP_FILE.'" method="get" name="postlist" id="postlist">';
		echo	'<input type="hidden" name="page" value="'.$page['page'].'">';
		echo	$inquiry_type=='trash'?'':'<input type="hidden" name="read" value="'.$read.'">';
		//echo	'<input type="hidden" name="datetime" value="'.date('Y-m-d',$datetime).'">';
		//echo	'<input type="hidden" name="end_datetime" value="'.date('Y-m-d',$end_datetime).'">';
        table_nav('top',$page_url);
        echo       '<table class="data-table" cellspacing="0">';
        echo           '<thead>';
        table_thead();
        echo           '</thead>';
        echo           '<tfoot>';
        table_thead();
        echo           '</tfoot>';		
				echo           '<tbody class="table-body">';
        if ($result) {
					while ($data = pages_fetch($result)) {
						$post     = post_get($data['id']); 
						$desc = mb_substr(clear_space(strip_tags($post['content'])),0,55,'UTF-8');
						if(strlen($desc)>54){
							$desc = $desc.'&hellip;';
						}else{
							$desc = $desc;
						}

				//$page = isset($_GET['page'])?$_GET['page']:0;
				$read = isset($_GET['read'])?$_GET['read']:null;
				
				//if($page=='') $page=0;
				
				$fromsite = mb_substr(clear_space(strip_tags(get_true_refer($post['website']))),0,60,'UTF-8');
				//print_r($post);
				//echo $data['id'];

        $view_url = 'inquiry.php?method=view&postid='.$post['id'].'&page='.$page['page'].'&read='.$read;
        //$post['count'] = comment_count($post['postid']);
        $actions = '<span class="view"><a href="'.$view_url.'" onclick="setnoread();">查看</a> | </span>';
				if(current_user_can('inquiry-mark',false)){
          $actions.= '<span class="noread"><a href="?page='.$page['page'].'&action=mark&postid='.$post['id'].'" title="标记为未读">未读</a> | </span>';
				}
				if(current_user_can('inquiry-delete',false)){
					$actions.= '<span class="delete"><a href="?page='.$page['page'].'&action=trash&postid='.$post['id'].'" title="移到垃圾箱">删除</a> | </span>';
				}else{
					$actions.= '';
				}                
				
				if($post['read']){
					$flag = 'read';
				}else{
					$flag = 'noread';
				}
				
				echo '<tr class="'.$flag.'">';
				echo    '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$post['id'].'" /></td>';
				echo    '<td><div title="'.$post['from_company'].'">'.$post['from_company'].'</div></td>';
				echo    '<td><div title="'.$post['content'].'">'.$desc.'</div><div class="row-actions">'.$actions.'</div></td>';
				
				
				echo    '<td><div title="'.$post['name'].'">'.$post['name'].'</div></td>';
				echo    '<td><div title="'.$post['email'].'">'.$post['email'].'</div></td>';
				echo    '<td><div title="'.$post['website'].'">'.$fromsite.'</div></td>';


				echo    '<td><div title="'.$post['user_agent'].'">'.$post['browser_name'] . " " . $post['browser_version']  . " " .$post['browser_platform'].'</div></td>';


				echo    '<td><div title="'.date('Y-m-d H:i:s',strtotime($post['time'])).'">'.date('Y-m-d H:i:s',strtotime($post['time'])).'</div></td>';//'.get_icon($post['approved']).'
                echo '</tr>';
            }
        } else {
            echo           '<tr><td colspan="8" class="tc">无项目!</td></tr>';
        }
        echo           '</tbody>';		
        echo       '</table>';

        table_nav('bottom',$page_url);
        echo   '</form>';
        echo '</div>';
        include 'footer.php';
        break;
}

function get_true_refer($url){
	$url = trim($url);
	
	//https://sbmzhcn.github.io/livechat/livechat-widgets.html?lng=en&r=https%3A%2F%2Fwww.bing.com%2F&p1=https%3A%2F%2Fwww.ingodsservice.org%2Fcharcoal-kiln-manufacturers-south-afrcia%2F
	if(strpos($url, "sbmzhcn.github")>0){
		$parsed_url = parse_url($url);

		// echo urldecode($parsed_url["query"]);
		$url_query = urldecode($parsed_url["query"]);
		parse_str($url_query, $parsed_str);

		if(isset($parsed_str["p1"]))
			return  $parsed_str["p1"] . " (" .$parsed_str["r"] . ")";
		return $url;
	}
	return $url;
	
}

/**
 * 批量操作
 *
 * @param  $side    top|bottom
 * @param  $url
 * @return void
 */
function table_nav($side,$url) {
    global $php_file, $category, $search, $inquiry_type, $read, $page;
    echo '<div class="table-nav">';
	if($side == 'top'){
		//echo    '<a class="table-nav-button" href="javascript:(void);" onclick="deleteData();" title="立即删除">删除</a>';
		echo	'<div class="alignleft actions">';
		echo		'<select name="action">';
		echo			'<option value="-1" selected="selected">批量动作</option>';
		echo			$inquiry_type=='trash'?'':'<option value="mark">标记未读</option>';
		echo			$read==0?'<option value="unmark">标记已读</option>':'';
		echo			$inquiry_type=='trash'?'<option value="untrash">恢复</option>':'<option value="trash">移到垃圾箱</option>';
		echo			'<option value="delete">彻底删除</option>';
		echo		'</select>';
		echo		'<input type="submit" name="" id="doaction" class="button-secondary action" value="应用">';
		
		echo        '<input type="text" class="span2" value="'.(isset($_REQUEST['datetime'])?$_REQUEST['datetime']:'').'" name="datetime" id="inputDate"  placeholder="2013-02-02">';
		echo		' - ';
		echo        '<input type="text" class="span2" value="'.(isset($_REQUEST['end_datetime'])?$_REQUEST['end_datetime']:date('Y-m-d',time())).'" name="end_datetime"  placeholder="2014-12-12" >';
		echo        '<input type="submit" class="button-secondary" value="按日期过滤">';
		
		echo		'<input type="hidden" name="_http_referer" value="'.$_SERVER['REQUEST_URI'].'">';
		echo		$inquiry_type=='trash'?'<input type="submit" name="delete_all" id="delete_all" class="button-secondary action" value="清空垃圾箱">':'';
		echo		$inquiry_type=='trash'?'<input type="hidden" name="type" value="trash">':'';
		//echo		'<input type="hidden" name="page" value="'.(isset($_GET['page'])?$_GET['page']:0).'">';
		echo	'</div>';
	}
	/*if(current_user_can('inquiry-mark',false)){	
	echo     '<a class="table-nav-button" href="javascript:(void);" onclick="markNoRead();">标记为未读</a>';
	}*/
    //echo     '<select name="actions">';
    //echo         '<option value="">更多操作</option>';
    //echo         '<option value="0">标记为未读</option>';
    //echo         '<option value="1">标记为已读</option>';
    //echo     '</select>';
/*	if ($side == 'top') {
        echo '<span class="filter">';
        if ('inquiry.php' == $php_file) {
            echo '<select name="read">';
            echo     '<option value="all">所有询盘</option>';
            echo     '<option value="no">未读询盘</option>';
			echo     '<option value="yes">已读询盘</option>';
            echo '</select>';
        }
        echo    '<input class="text" type="text" size="20" name="query" value="'.esc_html($search).'" />';
        echo    '<button type="submit">过滤</button>';
        echo '</span>';
    }*/
    
   	echo pages_list($url);
    echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
    global $php_file;
    echo '<tr>';
    echo     '<th class="check-column"><input type="checkbox" name="select" value="all"></th>';
    echo     '<th class=w100>网站公司名</th>';
    echo     '<th>内容</th>';
    echo     '<th class=w100>名字</th>';
    echo     '<th class=wp15>邮箱</th>';
    echo     '<th class=w250>来源网站</th>';
	echo     '<th class=w250>浏览器</th>';
    echo     '<th class=w150>时间</th>';
    echo '</tr>';
}
?>