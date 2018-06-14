<?php
set_time_limit(0);
header('Content-Type: text/html; charset=UTF-8');

    /* 连接选择数据库 */   
 
$link = mysql_connect("localhost", "root", "root")        or die("Could not connect");  
  print "Connected successfully";    
mysql_select_db("keywords") or die("Could not select database");
    /* 执行 SQL 查询 */    
 
$query = "SELECT url,page_title FROM `contents` where host='grindingmillforsale.com'";   
 $result = mysql_query($query) or die("Query failed");  
 
  /* 在 HTML 中打印结果 */  
 
 //print "<table>\n";   
 while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {        
	//print "\t<tr>\n";        
	foreach ($line as $col_value) {            
		//print "\t\t<td>$col_value</td>\n"; 
print $line->page_title;		
	}        
	//print "\t</tr>\n";    
 }    
 //print "</table>\n";
 
    /* 释放资源 */
    
//mysql_free_result($result);    
 
/* 断开连接 */   
 
mysql_close($link);
?>