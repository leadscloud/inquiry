function data_refresh(){
	var url=location.href;
	$('.table-body').load(url+" .table-body tr");
}

// 删除用户
function user_delete(userid){ 
  $.post('user.php', {method:'bulk', action:'delete', listids:userid}, function(data){
			if(data) {   
				$('.table-body').load("user.php .table-body tr"); 
				alert(data);  
			}else{  
				alert(data);  
			}  
			});
}
// 验证密码强弱
function user_check_pass_strength() {
	//alert('test');
    $('#pass-strength-result').check_pass_strength(
            $('#nickname').val(),
            $('#password1').val(),
            $('#password2').val()
    );
}
function deleteData(id){
	var del_arry=[];
	if(id==null){
			if($("input[name^=list]:checked").length==0){
				Boxy.alert("<p>你没有选择！</p>",null,{title:"友情提示"});
				return false;
			}else{
				Boxy.confirm("你确定删除吗？",function() {
			$('input[name=listids[]][checked]').each(function(i){  
				del_arry[i]=$(this).val();
				i++;
			});
			if (del_arry!=''){  
				$.post('../../includes/lib/json.php', {method:'bulk', action:'delete', listids:
				del_arry}, function(data){
			if(data==1) {   
				$('.table-body').load("inquiry.php .table-body tr");
				//window.location.reload();  
				Boxy.alert("删除成功");  
			}else{  
				Boxy.alert('删除失败！');  
			}  
			});
				
			}});
			}
	}else{
		Boxy.confirm("<span style=\"color:#ff0000\">你确定删除吗？</span>",function(){
			$.post('../../includes/lib/json.php', {method:'bulk', action:'delete', listids:
				new Array('0',id)}, function(data){
			if(data==1) {   
				$('.table-body').load("inquiry.php .table-body tr");
				$('.inside.comments').load("index.php .inside.comments .comment");
				//window.location.reload();  
				Boxy.alert(' 删除成功！');  
			}else{  
				Boxy.alert('删除失败！');  
			}  
			});
		},{title:"删除提示"});
		
	}
}
function markNoRead(id){
	var mark_arry=[];
	if(id==null){
	//var maked=false;
			$('input[name=listids[]][checked]').each(function(i){  
				mark_arry[i]=$(this).val();
				i++;
			});
			if (mark_arry!=''){  
				$.post('inquiry.php', {method:'bulk', action:'mark', listids:
				mark_arry}, function(data){
			//if(data==1) {   
				$('.table-body').load("inquiry.php .table-body tr");				
				//alert(data);
				//marked=true;
				//window.location.reload();    
			//}
			});
				
			} 
	}else{
		$.post('inquiry.php', {method:'bulk', action:'mark', listids:new Array('0',id)}, function(data){  
				$('.table-body').load("inquiry.php .table-body tr"); 
				});
	}
}
function setnoread(){
	//$('.table-body').load("inquiry.php .table-body tr");
}
function showcpmsgstr(msg) {
	$('#message').html('<p>'+msg+'</p>').show();
}
$(document).ready(function() {
	
/*	$('.read').tooltip({
      selector: "span[rel=tooltip]"
    });*/
	
	$('#postlist').actions();
	$('#userlist').actions();
	//cpanel_init();
	//$("a[rel=boxy]").boxy();
	// 记忆展开
    $('#admin-content').semiauto();
    // 表格背景变色
    $('.data-table tbody tr').hover(function(){
        //$(this).css({'background-color':'#FFFFCC'});
        $('.row-actions',this).css({'visibility': 'visible'});
    },function(){
        //$(this).css({'background-color':'#FFFFFF'});
        $('.row-actions',this).css({'visibility': 'hidden'});
    });
	// 表格背景变色
    $('.container .comment').hover(function(){
        $(this).css({'background-color':'#FFFFCC'});
        $('.row-actions',this).css({'visibility': 'visible'});
    },function(){
        $(this).css({'background-color':'#FFFFFF'});
        $('.row-actions',this).css({'visibility': 'hidden'});
    });
	// 绑定展开事件
	$('fieldset').each(function(i){
	    var fieldset = $(this);
	    $('a.toggle,h3',this).click(function(){
	        fieldset.toggleClass('closed');
	    });
	});
	//显示帮助
	
	$('#contextual-help-link').toggle(function() {
  		$(this).addClass("screen-meta-active");
		$('#screen-meta').slideDown();
		//$('#contextual-help-wrap').show();
	}, function() {
  		$(this).removeClass("screen-meta-active");
		$('#screen-meta').slideUp();
		//$('#contextual-help-wrap').hide();
	});
	
	
	
	$('#password1').val('').keyup( user_check_pass_strength );
    $('#password2').val('').keyup( user_check_pass_strength );
	
/*** 
		ZeroClipboard.setMoviePath( 'js/ZeroClipboard10.swf' );
		clip = new ZeroClipboard.Client();
			clip.setHandCursor( true );
				// update the text on mouse over
			var txt="";
			txt=$("#inquiryDetails").html();
			clip.setText(txt);
			clip.glue( 'd_clip_button', 'd_clip_container' );
			//clip.glue( 'd_clip_button2', 'd_clip_container2' );
			clip.addEventListener('complete', function(client) {
                showcpmsgstr("复制成功！");
            });
			
		clip2 = new ZeroClipboard.Client();
		clip2.setHandCursor( true );
		clip2.setText(txt);
		clip2.glue( 'd_clip_button2', 'd_clip_container2' );
		clip2.addEventListener('complete', function(client) {
                showcpmsgstr("复制成功！");
            });
***/

	var clipboard = new ClipboardJS('.btn-copy', {
		target: function(trigger) {
			//console.log("target", trigger);
			//return trigger.nextElementSibling;
		},
		text: function(trigger) {
			//console.log("text", trigger);
			return $("#inquiryDetails").html();
		}
	});

/** 
  document.addEventListener('copy', function(e) {
    //console.log("copy html", e);
    e.clipboardData.setData('text/html', e.text);
    e.clipboardData.setData('text/html', e.target.value);
    e.preventDefault();
  });
*/

  
	
		
	//检测电话号码所属国家
	var phone_num = $('#phone_num').text();
	
	var trim_phone_num =  phone_num.replace(/(^\s*)|(\s*)/g, ""); //去掉所有的空格
	var reg_phone_num = trim_phone_num.match(/\d{1,5}/);//匹配电话号码前4位，不过，如果代码为01758 只能匹配0175 实际是1785
	
	

	var htmls="";
	for(var i=0;i<wcArr.length;i++)
	{
		if(reg_phone_num && reg_phone_num[0].indexOf(wcArr[i][2])!=-1){
			htmls=htmls+"<span>"+wcArr[i][0]+" - " +wcArr[i][1]+' ('+wcArr[i][2]+')'+"</span>";
		}
	}
	if(phone_num!="")
		if(htmls=="") htmls ='<span>无匹配项！</span>';
		$("#phone_num").html(phone_num+"<span>查询结果：</span>"+htmls);
		
	
	
		
});