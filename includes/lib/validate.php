<?php
//defined('COM_PATH') or die('Restricted access!');

// 数字
define('VALIDATE_IS_NUMERIC','IS_NUMERIC');
// 字母
define('VALIDATE_IS_LETTERS','IS_LETTERS');
//电子邮箱
define('VALIDATE_IS_EMAIL','IS_EMAIL');
// 超链接
define('VALIDATE_IS_URL','IS_URL');
// 逗号分隔的数字列表
define('VALIDATE_IS_LIST','IS_LIST');
// 字母、数字、下划线、杠
define('VALIDATE_IS_CNUS','IS_CNUS');
// 字母、数字、下划线、杠、逗号、[、]
define('VALIDATE_IS_CNUSO','IS_CNUSO');
// 文件路径
define('VALIDATE_IS_PATH','IS_PATH');

// 不能为空
define('VALIDATE_EMPTY','EMPTY');
// 验证长度
define('VALIDATE_LENGTH','LENGTH');
// 两个值是否相等
define('VALIDATE_EQUAL','EQUAL');
//是否含有链接
define('VALIDATE_HAVE_LINK','HAVE_LINK');

/**
 * 系统验证类
 *
 */
class Validate {
    // private
    var $_error  = array();
    var $_isVal  = false;

    /**
     * 判断当前请求方法
     *
     * @return bool
     */
    function post(){
        return $_SERVER['REQUEST_METHOD']=='POST';
    }
    /**
     * 验证规则是否成立
     *
     * @return bool
     */
    function check(){
        $args = func_get_args();
        /**
         * 1.array('field',VALIDATE_TYPE,__('alert text'),params)
         * 2.array(
         *      array('field',VALIDATE_TYPE,__('alert text'),params),
         *      array('field',VALIDATE_TYPE,__('alert text'),params)
         *   )
         */
        if (is_array($args[0])) {
            // Use method 2 rule.
            if (is_array($args[0][0])) {
                foreach ($args[0] as $rule) {
                    if (!$this->check($rule)) break;
                }
            }
            // Use method 1 rule.
            else {
                return call_user_func_array(array(&$this,'check'),$args[0]);
            }
        } else {
            // Validate single rule.
            $error = false;
            $value = isset($_POST[$args[0]]) ? rawurldecode(trim($_POST[$args[0]])) : null; // POST值
            $type  = $args[1]; // 类型
            $text  = $args[2]; // 提示文字
            switch ($type) {
                case VALIDATE_EMPTY: case 'IS_EMPTY':
                    if (empty($value)) $error = $text;
                    break;
                case VALIDATE_LENGTH: case 'LENGTH_LIMIT':
                    if (!is_numeric($args[3]) && strpos($args[3],'-')!==false) {
                        $as = explode('-',$args[3]);
                        $args[3] = $as[0];
                        $args[4] = $as[1];
                    }
                    if (mb_strlen($value,'UTF-8') < (int)$args[3]
                        || mb_strlen($value,'UTF-8') > (int)$args[4]) {
                            if ($args[3]) {
                                $error = sprintf($text,$args[3],$args[4]);
                            } else {
                                $error = sprintf($text,$args[4]);
                            }
                        }
                    break;
                case VALIDATE_EQUAL: case 'IS_EQUAL':
                    $value1 = isset($_POST[$args[3]]) ? trim($_POST[$args[3]]) : null;
                    if ($value != $value1) $error = $text;
                    break;
				case VALIDATE_HAVE_LINK: case 'HAVE_LINK':
					if (strpos($value,'http')!==false||strpos($value,'url=')!==false) $error = $text;
					break;
                case false: case 'FALSE':
                    $error = $text;
                    break;
                default:
                    if (!$this->is($value,$type)) $error = $text;
                    break;
            }
            // 没有错误信息
            if (!$error) return true;
            $this->_set_error($args[0],$error);
            return false;
        }
    }
    /**
     * 静态验证方法
     *
     * @param string $str   需要验证的字符串
     * @param mixed  $type  验证类型，常量或者正则表达式
     * @return bool
     */
    function is($str,$type){
        switch ($type) {
            case VALIDATE_IS_NUMERIC: case 'IS_NUMERIC':
                $pattern = '/^\d+$/';
                break;
            case VALIDATE_IS_LETTERS: case 'IS_LETTERS':
                $pattern = '/^[a-z]+$/i';
                break;
            case VALIDATE_IS_EMAIL: case 'IS_EMAIL':
                $pattern = '/^\w+([\-\+\.]\w+)*@\w+([\-\.]\w+)*\.\w+([\-\.]\w+)*$/i';
                break;
            case VALIDATE_IS_URL: case 'IS_URL':
                $pattern = '/^(http|https|ftp)\:(\/\/|\\\\)(([\w\/\\\+\-~`@\:%])+\.)+([\w\/\\\.\=\?\+\-~`@\'\:!%#]|(&amp;)|&)+/i';
                break;
            case VALIDATE_IS_LIST: case 'IS_LIST':
                $pattern = '/^[\d\,\.]+$/i';
                break;
            case VALIDATE_IS_CNUS: case 'IS_CNUS':
                $pattern = '/^[\w\-]+$/i';
                break;
            case VALIDATE_IS_CNUSO: case 'IS_CNUSO':
                $pattern = '/^[\w\,\/\-\[\]]+$/i';
                break;
            case VALIDATE_IS_PATH: case 'IS_PATH':
                $pattern = '/^[^\:\*\<\>\|\\\\]+$/';
                break;
            default: // 自定义正则
                $pattern = $type;
                break;
        }
        return preg_match($pattern,$str);
    }
    /**
     * 验证结果有错误，输出错误
     *
     * @param bool $is_echo 是否输出错误
     * @return bool
     */
    function is_error($is_echo=true){
        if ($this->_error){
            if ($is_echo) {
				if (is_ajax()) {
                    ajax_echo('Validate',$this->_error);
                }else{
				   echo $this->_error[0]['text'];
				   echo '<br><a href="'.referer().'">Back</a>';
				}
                
            }
            return $this->_error;
        }
        return false;
    }
    /**
     * 设置错误信息
     *
     * @param string $id
     * @param string $text
     */
    function _set_error($id,$text){
        static $i = 0;
        $this->_error[$i]['id']   = $id;
        $this->_error[$i]['text'] = $text;
        $i++;
    }
}
/**
 * 取得验证实例
 *
 * @return Validate
 */
function &_validate_get_object() {
    static $validate;
	if ( is_null($validate) )
		$validate = new Validate();
	return $validate;
}
/**
 * 判断是否为post提交
 * 
 * @return bool
 */
function validate_is_post() {
    $validate = _validate_get_object();
    return $validate->post();
}
/**
 * 设置验证规则
 *
 * @return bool
 */
function validate_check() {
    $args = func_get_args();
    $validate = _validate_get_object();
    return $validate->check($args);
}
/**
 * 是否验证通过
 *
 * @return bool
 */
function validate_is_ok() {
    $validate = _validate_get_object();
    return !$validate->is_error();
}
/**
 * 验证方法
 *
 * @param  $str     需要验证的字符串
 * @param  $type    验证类型，常量或者正则表达式
 * @return bool
 */
function validate_is($str,$type) {
    $validate = _validate_get_object();
    return $validate->is($str,$type);
}