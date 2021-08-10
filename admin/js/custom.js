/**
 *
 * 外贸留言板系统
 * sbmzhcn@gmail.com
 * https://leadscloud.github.io/
 * 
 */
var Ray = window.Ray  = {
    // javascript libaray version
    version: '1.0',
    // 语言包对象
    L10n: {},
    // URI对象
    URI: {},
    // 后台根目录
    ADMIN: '/',
    // 站点根目录
    ROOT: '/',
  
    
    /**
     * 设置cookie
     *
     * @param name
     * @param key
     * @param val
     * @param options
     */
    setCookie: function(name,key,val,options) {
        options = options || {};
        var cookie  = $.cookie(name),
            opts    = $.extend({ expires: 365, path: Ray.ROOT }, options),
            cookies = cookie===null ? {} : Ray.parse_str(cookie);
        // 取值
        if (arguments.length == 2) {
            if (cookies[key]) return cookies[key];
            else return null;
        }
        // 赋值
        else {
            cookies[key] = val;
            return $.cookie(name, $.param(cookies), opts);
        }
    },
    /**
     * 取得cookie
     *
     * @param name
     * @param key
     */
    getCookie: function(name,key) {
        return Ray.setCookie(name,key);
    },
    /**
     * 等同于PHP parse_str
     * 
     * @param str
     */
    parse_str: function(str) {
        var pairs = str.split('&'),params = {}, urldecode = function(s){
            return decodeURIComponent(s.replace(/\+/g, '%20'));
        };
        $.each(pairs,function(i,pair){
            if ((pair = pair.split('='))[0]) {
                var key  = urldecode(pair.shift());
                var value = pair.length > 1 ? pair.join('=') : pair[0];
                if (value != undefined) value = urldecode(value);

                if (key in params) {
                    if (!$.isArray(params[key])) {
                        params[key] = [params[key]];
                    }
                    params[key].push(value);
                } else {
                    params[key] = value;
                }
            }
        });
        return params;
    }
};

Ray.URI.Host = (('https:' == self.location.protocol) ? 'https://'+self.location.hostname : 'http://'+self.location.hostname);
Ray.URI.Path = self.location.href.replace(/\?(.*)/,'').replace(Ray.URI.Host,'');
Ray.URI.File = Ray.URI.Path.split('/').pop();
Ray.URI.Path = Ray.URI.Path.substr(0,Ray.URI.Path.lastIndexOf('/')+1);
Ray.URI.Url  = Ray.URI.Host + Ray.URI.Path + Ray.URI.File;






/**
 * jQuery 扩展
 *
 */
(function ($) {
 
     // 绑定批量操作事件
    $.fn.actions = function(callback) {
        // 取得 action 地址
        var form   = $(this);

        // 绑定全选事件
        $('input[name=select]',form).click(function(){
            $('input[name^=list]:checkbox,input[name=select]:checkbox',form).attr('checked',this.checked);
        });
		

		$('button[rel=select]').click(function(){
			$('input[name^=roles]:checkbox').attr('checked',true);
        });
        // 表格背景变色
        $('tbody tr',form).hover(function(){
			$(this).addClass('hover');
            //$('td',this).css({'background-color':'#FFFFCC'});
            $('.row-actions',this).css({'visibility': 'visible'});
        },function(){
			$(this).removeClass('hover');
            //$('td',this).css({'background-color':'#FFFFFF'});
            $('.row-actions',this).css({'visibility': 'hidden'});
        });
       
    };
    
    // 半记忆功能
    $.fn.semiauto = function() {
        var name = Ray.URI.File.substr(0,Ray.URI.File.lastIndexOf('.')),
            opts = { path: Ray.URI.Path };
        // 下拉框处理
        $('select[cookie=true]',this).each(function(i){
            var t = $(this); t.attr('guid',i);
            var c = Ray.getCookie(name + '_select', 's' + i);
            if (c !== null) {
                $('option:selected',this).attr('selected',false);
                $('option[value=' + c + ']',this).attr('selected',true);
            }
        }).change(function(){
            Ray.setCookie(name + '_select', 's' + $(this).attr('guid'), this.value, opts);
        });
        // 多选处理
        $('input:checkbox[cookie=true]',this).each(function(i){
            var t = $(this); t.attr('guid',i);
            var c = Ray.getCookie(name + '_checkbox', 'c' + i);
            if (c !== null) {
                this.checked = c == 'true';
            }
        }).click(function(){
            Ray.setCookie(name + '_checkbox', 'c' + $(this).attr('guid'), this.checked, opts);
        });
        // 更多属性处理
        $('fieldset[cookie=true]',this).each(function(i){
            var t = $(this); t.attr('guid',i);
            var c = Ray.getCookie(name + '_fieldset', 'f' + i);
            if (c !== null) {
                t.toggleClass('closed', c == 'true');
            }
        }).find('a.toggle,h3').click(function(){
            Ray.setCookie(name + '_fieldset', 'f' + $(this).parents('fieldset').attr('guid'), !$(this).parents('fieldset').hasClass('closed'), opts);
        });
        return this;
    };

	// 初始化菜单
    $.fn.init_menu = function(){
        var mode  = Ray.setCookie('menu_setting', 'mode'),
            hover = function() {
                $('.folded li.head').unbind().hover(function(){
                    $('div.sub',this).addClass('open');
                },function(){
                    $('div.sub',this).removeClass('open');
                });
            };
        
        if (mode !== null) {
            $('#wrapper').toggleClass('folded',mode=='true');
            if (mode=='true') hover();
        }

        // 菜单模式切换
        $('li.separator',this).click(function(){
            $('#wrapper').toggleClass('folded'); hover();
			$('div.sub').each(function(){
				$('div.sub').removeAttr("style");
				$('div.sub').removeClass('open');
			});
            // 保存Cookie
            Ray.setCookie('menu_setting', 'mode', $('#wrapper').hasClass('folded'));
        });
        // 去掉虚线
        $('li.separator a',this).focus(function(){
            this.blur();
        });        
        // 下拉按钮点击的事件
        $('.head .toggle',this).click(function(){
			var head = $(this).parent();
				head.toggleClass('expand',$('.sub',head).slideToggle('fast',function(){
                    Ray.setCookie('menu_setting', 'm' + head.attr('menu_guid'), head.hasClass('expand'));
                }));
        });
        // 记录COOKIE
        $('.head',this).each(function(i){
            var t = $(this); t.attr('menu_guid',i);
            var c = Ray.getCookie('menu_setting','m' + i);
            if (c !== null && !t.hasClass('current')) {
                t.toggleClass('expand',c=='true');
            }
        });
    }
    
   
    /**
     * Create a cookie with the given name and value and other optional parameters.
     *
     * @example $.cookie('the_cookie', 'the_value');
     * @desc Set the value of a cookie.
     * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
     * @desc Create a cookie with all available options.
     * @example $.cookie('the_cookie', 'the_value');
     * @desc Create a session cookie.
     * @example $.cookie('the_cookie', null);
     * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
     *       used when the cookie was set.
     *
     * @param String name The name of the cookie.
     * @param String value The value of the cookie.
     * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
     * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
     *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
     *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
     *                             when the the browser exits.
     * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
     * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
     * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
     *                        require a secure protocol (like HTTPS).
     * @type undefined
     *
     * @name $.cookie
     * @cat Plugins/Cookie
     * @author Klaus Hartl/klaus.hartl@stilbuero.de
     */
    /**
     * Get the value of a cookie with the given name.
     *
     * @example $.cookie('the_cookie');
     * @desc Get the value of a cookie.
     *
     * @param String name The name of the cookie.
     * @return The value of the cookie.
     * @type String
     *
     * @name $.cookie
     * @cat Plugins/Cookie
     * @author Klaus Hartl/klaus.hartl@stilbuero.de
     */
    $.cookie = function(name, value, options) {
        if (typeof value != 'undefined') { // name and value given, set cookie
            options = options || {};
            if (value === null) {
                value = '';
                options.expires = -1;
            }
            var expires = '';
            if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
                var date;
                if (typeof options.expires == 'number') {
                    date = new Date();
                    date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                } else {
                    date = options.expires;
                }
                expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
            }
            // CAUTION: Needed to parenthesize options.path and options.domain
            // in the following expressions, otherwise they evaluate to undefined
            // in the packed version for some reason...
            var path = options.path ? '; path=' + (options.path) : '';
            var domain = options.domain ? '; domain=' + (options.domain) : '';
            var secure = options.secure ? '; secure' : '';
            document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
        } else { // only name given, get cookie
            var cookieValue = null;
            if (document.cookie && document.cookie != '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = jQuery.trim(cookies[i]);
                    // Does this cookie string begin with the name we want?
                    if (cookie.substring(0, name.length + 1) == (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }
    };
   
})(jQuery);