

## 外贸营销网站留言板程序

一个简单的留言板程序，在外贸网站运行测试多年没有任何问题。如果你的公司没有留言板程序，或者现在需要去找一个简单的留言板程序，这个很适合你。

本人之前在矿山机械行业工作，给公司开发过留言板程序并且运行多年。 这几天我重新写了一个留言板程序，希望可以帮助广大做外贸的同行们。

**特点：**

1. 自动过滤垃圾留言信息
2. 语言翻译功能集成
3. 一键复制留言信息

## 安装

1. 把文件上传到服务器根目录
2. 然后打开install.php, 例如： http://www.yourdomain.com/install.php
3. 安装完毕会在content目录下生成一个数据库

## 表单字段说明

具体的字段设置你可以在系统根目录下的updata.php里查看。

姓名与邮箱是必填的，其它都是非必须的。

submit.html有一个示例文件

```
<input type="hidden" name="from_company" value="YourName" />这个隐藏字段代表网站属于哪个牌子。
<input type="hidden" name="referer" value="http://www.youwantedsite.com" />这个隐藏字段代表你强制把referer改为某个网站。
```

字段：

`name` `email` `title` `content` `country` `phone` `address` `from_company`


### config.php 配置文件

下面是config里的内容。请修改DB_NAME,务必要以.php结尾，防止下载。BING_TRANSLATE_KEY Bing的翻译API，可以去注册一个。Akismet_API_Key 反垃圾服务，wordpress的反垃圾插件就是用它。根据此系统的域名，注册一个。有问题，再联系我，上次调试是没问题的。QQ:75504026

```php
<?php
//database name
define('DB_NAME','#inquiry_system.sqlite.php');
//database prefix
define('DB_PREFIX','wp');
//bing translate api key
//define('ACCOUNT_KEY', 'nujIh3e7l8Xs8CkP44xTHwu4Gaw0vV1xzaSgXZy');  已废弃
define('BING_TRANSLATE_KEY', '7724d07b364645asdfds8ca7dfsds49624');
//Akismet API Key
define('Akismet_API_Key','aaa7ab1s1df6e9');
//system root
define('BLOG_ROOT',dirname(__FILE__));
?>
```

## 联系我

QQ: 75504026

Email: love4026@gmail.com

Website: https://sbmzhcn.github.io

## 更新

### 2018-4-19

* bing翻译使用最新版本，去除之前的翻译
* 复制按钮使用原生的js即可，不用再使用flash技术了


## 关键词

留言板程序  简单留言板  外贸留言板  外贸营销  跨境电商留言板 