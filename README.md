

## 外贸营销网站留言板程序

一个简单的留言板程序，在外贸网站运行测试多年没有任何问题。如果你的公司没有留言板程序，或者现在需要去找一个简单的留言板程序，这个很适合你。

本人之前在矿山机械行业工作，给公司开发过留言板程序并且运行多年。 这几天我重新写了一个留言板程序，希望可以帮助广大做外贸的同行们。

**特点：**

1. 自动过滤垃圾留言信息
2. 语言翻译功能集成
3. 一键复制留言信息

> 这个留言板没想到需要的人还不少，已经有不少人询问我关于留言板的问题，有很多都愿意付费咨询。
> 这次修改了一些问题，功能上没什么改变。
> 这个代码已经有快10年了，比较老了，大家谨慎使用。

## 安装

1. 把文件上传到服务器根目录
2. 然后打开install.php, 例如： http://www.tinycms.xyz/install.php
3. 安装完毕会在`content`目录下生成一个数据库(默认是`#inquiry_system.sqlite.php`,不要被后缀名迷惑，它就是db数据库，可以用sqlite3打开)

LNMP一键安装包，需要注意，content文件夹权限问题
如果你是用root用户上传的文件，需要使用命令 `chown www:www content/`

## 关于跳转说明

提交留言成功后，不跳转到来源网页或者只跳转到首页等问题，这是因为Chrome默认不发送全部的http referer，导致出现问题。
这儿有详细说明：http://www.ruanyifeng.com/blog/2019/06/http-referer.html

### 建议操作：

**在config.php中添加以下代码**

```
define('SUBMIT_DONE_REDIRECT_JS', 'history.back();');
```
提交成功后，使用js的history.back返回来源网页。(*建议操作*)

或者按下面操作


**在你网站每个留言表单所在页面head部分添加以下代码**

```
<meta name="referrer" content="unsafe-url">
```

或 服务器中设置下 `header("Referrer-Policy: unsafe-url");`

这样每次提交留言，都会带上http referer的。


跳转到自定义页面，只需要设置下请求参数 `referer`

例如，`action="https://tinycms.xyz/update.php?referer=https%3A%2F%2Fleadscloud.github.io%2Fserp-analyzer%2F%3Futm_media%3Dpc"`

提交成功后会跳转到 `https://leadscloud.github.io/serp-analyzer/?utm_media=pc`


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

## 软件截图

![](https://github.com/leadscloud/inquiry/blob/master/docs/inquiry-screen01.png?raw=true)

![](https://github.com/leadscloud/inquiry/blob/master/docs/inquiry-screen02.png?raw=true)

![](https://github.com/leadscloud/inquiry/blob/master/docs/inquiry-screen03.png?raw=true)

## 联系我


关于的系统的方面的问题，请在github的[issue](/../../issues)中提交。


~~QQ: 75504026~~

Website: https://leadscloud.github.io

## 推广

* 提供域名自动筛选及抢注服务，有需要请联系！
* 提供自动上站系统，可批量上线SEO网站(包括采集程序，可以采集Google搜索结果)。
* 提供服务器运维，网站制作。

## 更新

### 2021-10-09

* 修复一个安装时数据库更名后无法使用的问题

### 2021-08-10

* 修复下提交成功跳转问题
* 更新Bing翻译API
* 格式化代码

### 2018-7-15

* 修改安装的一些逻辑判断的问题
* 留言内容复制功能导致默认的复制功能不能用的问题
* 其它一些样式的小问题

### 2018-4-19

* bing翻译使用最新版本，去除之前的翻译
* 复制按钮使用原生的js即可，不用再使用flash技术了

### 修改数据库

```
 ALTER TABLE table_name
  ADD column_1 column-definition,
      column_2 column-definition,
      ...
      column_n column_definition;
```


## 关键词

留言板程序  简单留言板  外贸留言板  外贸营销  跨境电商留言板 机械行业互联网营销推广 上海 郑州 北京