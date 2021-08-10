<?php
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
$_USER = user_current();
system_head('title','推广');
include 'header.php';
?>
<div class="wrap promotion">
	<h2>推广</h2>
	<div class="clear"></div>
	<p>下面是推广我的一些作品和能力，如果有需要请联系我！</p>
	<ul>
		<li>Chrome扩展：显示搜索引擎结果页，每个域名的所属人 <a href="https://leadscloud.github.io/serp-analyzer/" target="_blank">链接</a></li>
		<li>TinyCMS：SEO网站系统，一台服务器承载上千个网站 <a href="https://www.tinycms.xyz/" target="_blank">链接</a></li>
		<li>RliveChat JS：一个移动优先的前端显示留言浮窗口的JS脚本 <a href="https://cdn.livechatinc.xyz/" target="_blank">链接</a></li>
		<li>LeadsCloud：域名过滤，域名抢注，询盘管理，自动上站系统 <a href="http://wpa.qq.com/msgrd?v=3&uin=75504026&site=qq&menu=yes" target="_blank">QQ联系我</a></li>
		<li>infomaster：询盘管理系统，之前世邦使用的 <a href="https://github.com/leadscloud/infomaster" target="_blank">源码链接</a></li>
		<li>SEO采集站程序：几个采集网站显示程序（PHP） <a href="https://github.com/leadscloud/seo-cms" target="_blank">源码链接</a></li>
		<li>采集程序：采集Google等几十个搜索引擎结果页的工具，非常完善。 </li>
		<li>服务器运维，Hack。</li>
		<li>网站制作，静态网站hugo, 动态网站wordpress。</li>
		<li>各行业询盘提供，及营销系统咨询服务。</li>
		<li>SEO优化。</li>
	</ul>
	<p>微信扫一扫联系我：</p>
	<img src="https://raw.githubusercontent.com/leadscloud/resume/master/resume/weixin.png" alt="联系我">
</div>
<?php
include 'footer.php';
?>