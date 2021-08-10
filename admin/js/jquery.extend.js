/**
 * jQuery 扩展
 *
 */
(function ($) {
    /**
     * 在光标位置插入值
     *
     * @param val
     */
    $.fn.insertVal = function(val) {
        return this.each(function(){
            // IE support
            if (document.selection && document.selection.createRange){
                this.focus();
                var sel = document.selection.createRange();
                    if (sel.text) {
                        sel.text += val;
                    } else {
                        this.value += val;
                    }
            }
            // MOZILLA/NETSCAPE support
            else if (this.selectionStart || this.selectionStart == '0') {
                var start = this.selectionStart, end = this.selectionEnd, stp = this.scrollTop;
                this.value = this.value.substring(0, start) + val + this.value.substring(end, this.value.length);
                this.focus();
                this.selectionStart = start + val.length;
                this.selectionEnd = start + val.length;
                this.scrollTop = stp;
            }
            // Other
            else {
                this.value += val;
                this.focus();
            }
        });
    };
    /**
     * 检查密码强度
     *
     * @param user
     * @param pass1
     * @param pass2
     */
    $.fn.check_pass_strength = function(user,pass1,pass2) {
        this.removeClass('short bad good strong');
        if ( ! pass1 ) {
            return this.html( '密码强度指示器' );
        }
        // Password strength meter
        var password_strength = function(username, password1, password2) {
            var short_pass = 1, bad_pass = 2, good_pass = 3, strong_pass = 4, mismatch = 5, symbol_size = 0, natLog, score;
                username = username || '';

            // password 1 != password 2
            if ( (password1 != password2) && password2.length > 0)
                return mismatch

            //password < 4
            if ( password1.length < 4 )
                return short_pass

            //password1 == username
            if ( password1.toLowerCase() == username.toLowerCase() )
                return bad_pass;

            if ( password1.match(/[0-9]/) )
                symbol_size +=10;
            if ( password1.match(/[a-z]/) )
                symbol_size +=26;
            if ( password1.match(/[A-Z]/) )
                symbol_size +=26;
            if ( password1.match(/[^a-zA-Z0-9]/) )
                symbol_size +=31;

            natLog = Math.log( Math.pow(symbol_size, password1.length) );
            score = natLog / Math.LN2;

            if (score < 40 )
                return bad_pass

            if (score < 56 )
                return good_pass

            return strong_pass;
        };

        var strength = password_strength(user, pass1, pass2);

        switch ( strength ) {
            case 2:
                this.addClass('bad').html( '弱' );
                break;
            case 3:
                this.addClass('good').html( '中等' );
                break;
            case 4:
                this.addClass('strong').html( '强' );
                break;
            case 5:
                this.addClass('short').html( '不匹配' );
                break;
            default:
                this.addClass('short').html( '非常弱' );
        }
        return this;
    };
    /*
     * JSON  - JSON for jQuery
     *
     * FILE:jquery.json.js
     *
     * Example:
     *
     * $.toJSON(Object);
     * $.parseJSON(String);
     */
    $.toJSON = function(o){
        var i, v, s = $.toJSON, t;
        if (o == null) return 'null';
        t = typeof o;
        if (t == 'string') {
            v = '\bb\tt\nn\ff\rr\""\'\'\\\\';
            return '"' + o.replace(/([\u0080-\uFFFF\x00-\x1f\"])/g, function(a, b) {
                i = v.indexOf(b);
                if (i + 1) return '\\' + v.charAt(i + 1);
                a = b.charCodeAt().toString(16);
                return '\\u' + '0000'.substring(a.length) + a;
            }) + '"';
        }
        if (t == 'object') {
            if (o instanceof Array) {
                for (i=0, v = '['; i<o.length; i++) v += (i > 0 ? ',' : '') + s(o[i]);
                return v + ']';
            }
            v = '{';
            for (i in o) v += typeof o[i] != 'function' ? (v.length > 1 ? ',"' : '"') + i + '":' + s(o[i]) : '';
            return v + '}';
        }
        return '' + o;
    };
})(jQuery);