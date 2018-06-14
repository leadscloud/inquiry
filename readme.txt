
下面是config里的内容。请修改DB_NAME,务必要以.php结尾，防止下载。ACCOUNT_KEY Bing的翻译API，可以去注册一个。Akismet_API_Key 反垃圾服务，wordpress的反垃圾插件就是用它。根据此系统的域名，注册一个。有问题，再联系我，上次调试是没问题的。QQ:75504026
可以查看submit.html这是一个例子。

<input type="hidden" name="from_company" value="YourName" />这个隐藏字段代表网站属于哪个牌子。
<input type="hidden" name="referer" value="http://www.youwantedsite.com" />这个隐藏字段代表你强制把referer改为某个网站。

具体的字段设置你可以在系统根目录下的updata.php里查看。

姓名与邮箱是必填的，其它都是非必须的。

安装文件地址：/install.php

<?php
//database name
define('DB_NAME','#inquiry_20121214_db_asfdjiecdeps.sqlite.php');
//database prefix
define('DB_PREFIX','wp');
//bing translate api key
define('BING_TRANSLATE_KEY', 'nujIh3e7l8Xs8CkP44xTHwu4Gaw0vV1xzaSgXZy');
//Akismet API Key
define('Akismet_API_Key','aaa7ab1fd6e9');
//system root
define('BLOG_ROOT',dirname(__FILE__));
?>


升级说明：

使用upgrade_db.php升级旧的系统。