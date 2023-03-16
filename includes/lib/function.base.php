<?php
/**************************************************************
 * 
 * 外贸留言板系统 
 * 
 * 如果你使用此系统，请保留版本声明。
 * 
 * Copyright (c) Ray
 * Email: <sbmzhcn@gmail.com>
 * Website: https://leadscloud.github.io/
 * 
 ***************************************************************/

// Http scheme
define('HTTP_SCHEME', (($scheme = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null) == 'off' || empty($scheme)) ? 'http' : 'https');

defined('COOKIE_DOMAIN') or define('COOKIE_DOMAIN', '');


// Http host
define('HTTP_HOST', HTTP_SCHEME . '://' . $_SERVER['HTTP_HOST']);

// System version
define('SYS_VERSION', '5.0.Beta');


// Web root
//define('ROOT',str_replace('\\','/',substr(dirname(PHP_FILE),0,-strlen(substr(realpath('.'),strlen(ABS_PATH)))+1)));

/**
 * PDO数据库操作类
 */

class db
{
    private $config;

    private $db;

    public $querynum;

    // public
    var $ready  = false;
    var $conn   = null;
    var $sql    = '';
    var $name   = 'test';
    var $prefix = '';
    var $scheme = null;

    public function __construct($dbfile=null, $prefix=null) {
        $this->prefix = $prefix;
        $this->name = $dbfile;
    }

    public function mysql($host, $user, $password, $dbname, $tablepre = '', $charset = 'utf-8')
    {
        $this->config['type'] = 'mysql';
        $this->config['tablepre'] = $tablepre;
        $this->config['mysql']['host'] = $host;
        $this->config['mysql']['user'] = $user;
        $this->config['mysql']['password'] = $password;
        $this->config['mysql']['dbname'] = $dbname;
        $this->config['mysql']['charset'] = $charset;
    }

    public function sqlite($datafile, $tablepre = '')
    {
        $this->config['type'] = 'sqlite';
        $this->config['sqlite']['file'] = $datafile;
        $this->config['tablepre'] = $tablepre;
    }

    public function create_db($datafile)
    {
        if (!file_exists($datafile)) {
            //exit('System is not installed, please install it !');
            new PDO(sprintf('sqlite:%s', $datafile));
        }
    }

    private function connect()
    {
        if (isset($this->db)) {
            return true;
        }
        if ($this->config['type'] == 'mysql') {
            try {
                $this->db = new PDO('mysql:host=' . $this->config['mysql']['host'] . ';dbname=' . $this->config['mysql']['dbname'], $this->config['mysql']['user'], $this->config['mysql']['password'], array(PDO::ATTR_PERSISTENT => true));
                $this->db->query('SET NAMES ' . $this->config['mysql']['charset']);
            } catch (PDOException $e) {
                exit('数据库连接失败：' . $e->getMessage());
            }
        }
        if ($this->config['type'] == 'sqlite') {
            if (!file_exists($this->config['sqlite']['file'])) {
                //echo 'Please install system!  <a href="/install.php">Install...</a>';
                //return;
            }
            //!file_exists($this->config['sqlite']['file']) && exit('Please install system!  <a href="/install.php">Install...</a>');

            if (!file_exists($this->config['sqlite']['file'])) {
                try {
                    //create or open the database
                    $this->db = new PDO('sqlite:' . $this->config['sqlite']['file']);
                } catch (Exception $e) {
                    die($error);
                }
                //$db=new SQLite($this->config['sqlite']['file']);  //这个数据库文件名字任意
            }

            $this->db = new PDO('sqlite:' . $this->config['sqlite']['file']);
        }
        !isset($this->db) && exit('不支持该数据库类型 ' . $this->config['type']);
    }

    public function table($table)
    {
        return '`' . $this->config['tablepre'] . $table . '`';
    }

    public function strescape($str)
    {
        if ($this->config['type'] === 'mysql') {
            return !get_magic_quotes_gpc() ? addslashes($str) : $str;
        }
        if ($this->config['type'] === 'sqlite') {
            return str_replace('\'', '\'\'', $str);
        }
        return $str;
    }

    public function format_condition($condition)
    {
        if (is_array($condition)) {
            foreach ($condition as $key => $value) {
                $join[] = $key . ' = \'' . $this->strescape($value) . '\'';
            }
            return ' WHERE ' . join(' AND ', $join);
        }
        return $condition ? ' WHERE ' . $condition : '';
    }

    private function error()
    {
        if ($this->db->errorCode() != '00000') {
            $error = $this->db->errorInfo();
            exit('SQL语句错误：' . $error['2']);
        }
    }
    /**
     * 执行查询
     *
     * @param string $sql
     * @return bool
     */
    function query($sql)
    {
        $this->connect();

        $args = func_get_args();

        $sql = preg_replace('/#@/', defined('DB_PREFIX') ? DB_PREFIX : $this->prefix, $sql);

        //$sql = call_user_func_array(array(&$this,'prepare'), $args);
        //$sql = $this->process($sql, $befores, $afters);

        if (preg_match("/^\\s*(insert|delete|update|replace|alter table|create) /i", $sql)) {
            $func = 'exec';
        } else {
            $func = 'query';
        }
        // 执行前置sql
        // if ($befores) {
        //     foreach ($befores as $v) $this->query($v);
        // }
        // $this->sql = $sql;
        $result = $this->db->$func($sql);
        if ($this->errno() != 0) {
            return exit(sprintf('SQLite 查询错误：%s', $sql . "\r\n\t" . $this->error()));
        }
        // 查询正常
        else {
            // 执行后置 SQL
            // if ($afters) {
            //     foreach ($afters as $v) $this->query($v);
            // } 
            // 返回结果
            if ($func == 'exec') {
                if (preg_match("/^\\s*(insert|replace) /i", $sql)) {
                    $result = ($insert_id = $this->db->lastInsertId()) >= 0 ? $insert_id : $this->result("SELECT LAST_INSERT_ROWID();");
                } else {
                    $result = $this->result("SELECT CHANGES();");
                }
            }
        }
        return $result;
    }
    //    	public function query($sql)
    // {
    // 	$this->connect();
    // 	$sql = preg_replace('/#@/',DB_PREFIX,$sql);
    // 	$result = $this->db->query($sql);
    // 	$this->error();
    // 	$result->setFetchMode(PDO::FETCH_ASSOC);
    // 	$this->querynum++;
    // 	return $result;
    // } 

    /**
     * 处理 SQLite
     *
     * @param string $sql
     * @param array &$before
     * @param array &$after
     * @return string
     */
    function process($sql, &$before = null, &$after = null)
    {
        $sql = str_replace('`', '"', $sql);
        $result = $after = array();
        $charlist = '`"[]\'';
        if (preg_match('/^(\s*CREATE\s+TABLE\s+)(IF\s+NOT\s+EXISTS\s+([^\s]+)|[^\s]+)/is', $sql, $matches)) {
            $table = isset($matches[3]) ? $matches[3] : $matches[2];
            // 版本低于3.0 AND 表已存在
            if (
                version_compare($this->version(), '3.0.0', '<')
                && preg_match('/^IF\s+NOT\s+EXISTS/i', $matches[2])
                && $this->is_table(trim($table, $charlist))
            ) {
                $sql = 'SELECT 1;';
            } else {
                preg_match('/\((.*)\)/ms', $sql, $match);
                $inner = trim($match[1]);
                $inner = str_ireplace(' unsigned', '', $inner);
                $lines = explode("\n", $inner);
                foreach ($lines as $line) {
                    $line = rtrim(trim($line), ',');
                    // 处理主键
                    if (preg_match('/^PRIMARY\s*KEY.+$/i', $line)) continue;
                    // 处理唯一索引
                    if (preg_match('/^UNIQUE\s*KEY\s*([^ ]+)\s*(\(.+\))$/i', $line, $match)) {
                        $after[] = sprintf('CREATE UNIQUE INDEX `%2$s_%1$s` ON `%2$s` %3$s;', trim($match[1], $charlist), trim($table, $charlist), $match[2]);
                        continue;
                    }
                    // 处理普通索引
                    if (preg_match('/^KEY\s*([^ ]+)\s*(\(.+\))$/i', $line, $match)) {
                        $after[] = sprintf('CREATE INDEX `%2$s_%1$s` ON `%2$s` %3$s;', trim($match[1], $charlist), trim($table, $charlist), $match[2]);
                        continue;
                    }
                    // 处理自动编号
                    if (strpos($line, 'AUTO_INCREMENT') !== false) {
                        preg_match('/^([^ ]+).+/i', $line, $match);
                        $line = sprintf('%s INTEGER PRIMARY KEY NOT NULL', $match[1]);
                    }
                    // 处理字段类型
                    $line = preg_replace('/ NOT\s*NULL/i', '', $line);
                    $line = preg_replace('/ (bigint|int|smallint|tinyint)\([0-9]+\)/i', ' INTEGER', $line);
                    $line = preg_replace('/ (char\([0-9]+\)|(tinytext|text|longtext))/i', ' TEXT', $line);
                    $line = preg_replace('/ enum\([^\)]+\)/i', ' TEXT', $line);
                    $line = preg_replace('/ decimal(\([^\)]+\))/i', ' NUMERIC\1', $line);
                    $line = preg_replace('/ varchar(\([^\)]+\))/i', ' VARCHAR\1', $line);
                    $line = preg_replace('/ timestamp/i', ' TIMESTAMP', $line);
                    $result[] = $line;
                }
                if (version_compare($this->version(), '3.0.0', '<')) {
                    $sql = sprintf("%s%s (\n%s\n);", $matches[1], $table, implode(",\n", $result));
                } else {
                    $sql = sprintf("%s (\n%s\n);", $matches[0], implode(",\n", $result));
                }
            }
        }
        // SELECT COUNT(DISTINCT("postid"))
        elseif (version_compare($this->version(), '3.0.0', '<') && preg_match('/^(\s*SELECT\s+)COUNT\s*\(\s*(DISTINCT\s*\(\s*[^\)]+\s*\))\s*\)(\s+FROM )/isU', $sql, $matches)) {
            $create_view = preg_replace('/^(\s*SELECT\s+)COUNT\s*\(\s*(DISTINCT\s*\(\s*[^\)]+\s*\))\s*\)(\s+FROM )/is', '\1\2\3', $sql);
            $view_name   = md5($create_view);
            $create_view = sprintf('CREATE TEMP VIEW "%s" AS %s;', $view_name, $create_view);
            $before[]    = $create_view;
            $sql         = sprintf('SELECT COUNT(*) FROM "%s";', $view_name);
        }
        // SELECT
        elseif (preg_match('/^\s*SELECT .+ FROM /is', $sql, $matches)) {
            $sql = preg_replace('/BINARY\s*/i', '', $sql);
        }
        // TODO alter table
        elseif (false) {
        }
        return $sql;
    }
    /**
     * SQLite 版本
     *
     * @return string
     */
    function version()
    {
        return $this->db->getAttribute(PDO::ATTR_CLIENT_VERSION);
    }
    /**
     * 错误码
     *
     * @return int
     */
    function errno()
    {
        $errno = $this->db->errorCode();
        return empty($errno) || $errno == '00000' ? 0 : $errno;
    }
    /**
     * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
     *
     * The following directives can be used in the query format string:
     *   %d (decimal number)
     *   %s (string)
     *   %% (literal percentage sign - no argument needed)
     *
     * Both %d and %s are to be left unquoted in the query string and they need an argument passed for them.
     * Literals (%) as parts of the query must be properly written as %%.
     *
     * This function only supports a small subset of the sprintf syntax; it only supports %d (decimal number), %s (string).
     * Does not support sign, padding, alignment, width or precision specifiers.
     * Does not support argument numbering/swapping.
     *
     * May be called like {@link http://php.net/sprintf sprintf()} or like {@link http://php.net/vsprintf vsprintf()}.
     *
     * Both %d and %s should be left unquoted in the query string.
     *
     *
     * @param string $query Query statement with sprintf()-like placeholders
     * @param array|mixed $args The array of variables to substitute into the query's placeholders if being called like
     * 	{@link http://php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if
     * 	being called like {@link http://php.net/sprintf sprintf()}.
     * @param mixed $args,... further variables to substitute into the query's placeholders if being called like
     * 	{@link http://php.net/sprintf sprintf()}.
     * @return null|false|string Sanitized query string, null if there is no query, false if there is an error and string
     * 	if there was something to prepare
     */
    function prepare($query = null)
    { // ( $query, *$args )
        if (is_null($query)) return;
        $args = func_get_args();
        array_shift($args);
        // If args were passed as an array (as in vsprintf), move them up
        if (isset($args[0]) && is_array($args[0])) $args = $args[0];

        $query = str_replace("'%s'", '%s', $query); // in case someone mistakenly already singlequoted it
        $query = str_replace('"%s"', '%s', $query); // doublequote unquoting
        $query = preg_replace('/(?<!%)%s/', "'%s'", $query); // quote the strings, avoiding escaped strings like %%s
        // 处理表前缀
        if (preg_match_all("/'[^']+'/", $query, $r)) {
            foreach ($r[0] as $i => $v) {
                $query = preg_replace('/' . preg_quote($v, '/') . '/', "'@{$i}@'", $query, 1);
            }
        }
        $query = preg_replace('/#@_([^ ]+)/iU', $this->prefix . '$1', $query);
        if (isset($r[0]) && !empty($r[0])) {
            foreach ($r[0] as $i => $v) {
                $query = str_replace("'@{$i}@'", $v, $query);
            }
        }
        if ($args) {
            foreach ($args as $k => $v) {
                $args[$k] = $this->escape($v);
            }
            $query = vsprintf($query, $args);
        }
        return $query;
    }

    public function exec($sql)
    {
        $this->connect();
        $sql = preg_replace('/#@/', DB_PREFIX, $sql);
        $result = $this->db->exec($sql);
        $this->error();
        $this->querynum++;
        return $result;
    }

    /**
     * 判断列名是否存在
     *
     * @param string $p1    table
     * @param string $p2    field
     * @return bool
     */
    function is_field($table, $field)
    {
        return in_array($field, $this->get_fields($table));
    }



    public function lastinsertid()
    {
        return $this->db->lastInsertId();
    }



    public function fetchall($table, $field, $condition = '', $sort = '', $limit = '')
    {
        $condition = $this->format_condition($condition);
        $sort && $sort = ' ORDER BY ' . $sort;
        $limit && $limit = ' LIMIT ' . $limit;
        $sql = 'SELECT ' . $field . ' FROM ' . $this->table($table) . $condition . $sort . $limit;
        return $this->query($sql)->fetchall();
    }

    //public function fetch($table, $field, $condition = '', $sort = '')
    //{
    //$condition = $this->format_condition($condition);
    //$sort && $sort = ' ORDER BY '.$sort;
    //$sql = 'SELECT '.$field.' FROM '.$this->table($table).$condition.$sort.' LIMIT 1';
    //return $this->query($sql)->fetch();
    //}


    /**
     * 取得数据集的单条记录
     *
     * @param PDOStatement $result
     * @param int $mode
     * @return array
     */
    function fetch($result, $mode = 1)
    {

        switch (intval($mode)) {
            case 0:
                $mode = PDO::FETCH_NUM;
                break;
            case 1:
                $mode = PDO::FETCH_ASSOC;
                break;
            case 2:
                $mode = PDO::FETCH_BOTH;
                break;
        }

        return $result->fetch($mode);
    }

    function get_results($query = null)
    {
        return $this->query($query)->fetch();
    }

    function fetch_result($query = null)
    {
        $value =  array_values($this->query($query)->fetch());
        return  $value[0];
    }

    /**
     * 等同于 mysql_result
     *
     * @param string $sql 可以是MYSQL资源句柄，也可以使用MYSQL语句
     * @param int $row 偏移量
     * @return string
     */
    function result($sql, $row = 0)
    {
        $result = $this->query($sql);
        if (!$result) return null;
        if ($rs = $this->fetch($result, 0)) {
            return $rs[$row];
        }
        return null;
    }



    public function rowcount($sql)
    {
        //$condition = $this->format_condition($condition);
        //$sql = 'SELECT COUNT(*) FROM '.$this->table($table).$condition;
        $result = $this->query($sql)->fetch();
        return $result['COUNT(*)'];
    }

    public function get_fields($table)
    {
        $result = array();
        if ($this->config['type'] == 'mysql') {
            $sql = 'DESCRIBE ' . $table;
            $key = 'Field';
        } else if ($this->config['type'] == 'sqlite') {
            $sql = 'PRAGMA table_info(' . $table . ')';
            $key = 'name';
        }
        $fields = $this->query($sql)->fetchall();
        foreach ($fields as $value) {
            $result[] = $value[$key];
        }
        return $result;
    }
    /**
     * 列出表里的所有字段
     *
     * @param string $table    表名
     */
    function list_fields($table)
    {
        $result = array();
        $res    = $this->query(sprintf("PRAGMA table_info(%s);", $table));
        while ($row = $this->fetch($res)) {
            $result[] = $row['name'];
        }
        return $result;
    }
    /**
     * 插入数据
     *
     * @param string $table    table
     * @param array  $data     插入数据的数组，key对应列名，value对应值
     * @return int
     */
    function insert($table, $data)
    {
        $cols = array();
        $vals = array();
        foreach ($data as $col => $val) {
            $cols[] = $this->identifier($col);
            $vals[] = $this->escape($val);
        }

        $sql = "INSERT INTO "
            . $this->identifier($table)
            . ' (' . implode(', ', $cols) . ') '
            . "VALUES ('" . implode("', '", $vals) . "')";

        return $this->query($sql);
    }
    /**
     * 更新数据
     *
     * @param string $table    table
     * @param array  $sets     set 数组
     * @param mixed  $where    where语句，支持数组，数组默认使用 AND 连接
     * @return int
     */
    function update($table, $sets, $where = null)
    {
        // extract and quote col names from the array keys
        $set = array();
        foreach ($sets as $col => $val) {
            $val   = $this->escape($val);
            $set[] = $this->identifier($col) . " = '" . $val . "'";
        }
        $where = $this->where($where);
        // build the statement
        $sql = "UPDATE "
            . $this->identifier($table)
            . ' SET ' . implode(', ', $set)
            . (($where) ? " WHERE {$where}" : '');

        return $this->query($sql);
    }
    /**
	public function update($table, $array, $condition)
	{
		if (!is_array($array)) {
			return false;
		}
		$condition = $this->format_condition($condition);
		foreach ($array as $key => $value) {
			$vals[] = $key.' = \''.$this->strescape($value).'\'';
		}
		$values = join(',', $vals);

		$sql = "UPDATE "
             . $this->table($table)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE {$where}" : '');

		$sql = 'UPDATE '.$this->table($table).' SET '.$values.$condition;
		return $this->exec($sql);
	}
     **/

    /**
     * 删除数据
     *
     * @param string $table
     * @param string $where
     * @return int
     */
    function delete($table, $where = null)
    {
        $where = $this->where($where);
        // build the statement
        $sql = "DELETE FROM "
            . $this->identifier($table)
            . (($where) ? " WHERE {$where}" : '');

        return $this->query($sql);
    }


    function getServerVersion()
    {
        $this->db = new PDO('sqlite:' . $this->config['sqlite']['file']);
        $ver = $this->db->getAttribute(PDO::ATTR_SERVER_VERSION);
        return $ver;
    }

    /**
     * where语句组合
     *
     * @param mixed $data where语句，支持数组，数组默认使用 AND 连接
     * @return string
     */
    function where($data)
    {
        if (empty($data)) {
            return '';
        }
        if (is_string($data)) {
            return $data;
        }
        $cond = array();
        foreach ($data as $field => $value) {
            $cond[] = "(" . $this->identifier($field) . " = '" . $this->escape($value) . "')";
        }
        $sql = implode(' AND ', $cond);
        return $sql;
    }

    /**
     * 判断数据表是否存在
     *
     * 注意表名的大小写，是有区别的
     *
     * @param string $table    table
     * @return bool
     */
    function is_table($table)
    {
        $res = $this->query("SELECT `name` FROM `sqlite_master` WHERE `type`='table';");
        //if (!strncasecmp($table,'#@',3))
        $table = str_replace('#@', DB_PREFIX, $table);
        while ($rs = $this->fetch($res, 0)) {
            if ($table == $rs[0]) return true;
        }
        return false;
    }
    /**
     * 转义变量
     *
     * @param mixed $value
     * @return string
     */
    function envalue($value)
    {
        // 空
        if ($value === null) return '';
        // 不是标量
        if (!is_scalar($value)) {
            // 是数组列表
            if (is_array($value) && !is_assoc($value)) {
                $value = implode(',', $value);
            }
            // 需要序列化
            else {
                $value = serialize($value);
            }
        }
        return $value;
    }
    /**
     * 转义SQL语句
     *
     * @param mixed $value
     * @return string
     */
    function escape($value)
    {
        // 空
        if ($value === null) return '';
        // 转义变量
        $value = $this->envalue($value);

        return str_replace("'", "''", $value);
    }
    /**
     * 转义SQL关键字
     *
     * @param string $filed
     * @return string
     */
    function identifier($filed)
    {
        $result = null;
        // 检测是否是多个字段
        if (strpos($filed, ',') !== false) {
            // 多个字段，递归执行
            $fileds = explode(',', $filed);
            foreach ($fileds as $k => $v) {
                if (empty($result)) {
                    $result = $this->identifier($v);
                } else {
                    $result .= ',' . $this->identifier($v);
                }
            }
            return $result;
        } else {
            // 解析各个字段
            if (strpos($filed, '.') !== false) {
                $fileds = explode('.', $filed);
                $_table = trim($fileds[0]);
                $_filed = trim($fileds[1]);
                $_as    = chr(32) . 'AS' . chr(32);
                if (stripos($_filed, $_as) !== false) {
                    $_filed = sprintf("`%s`%s`%s`", trim(substr($_filed, 0, stripos($_filed, $_as))), $_as, trim(substr($_filed, stripos($_filed, $_as) + 4)));
                }
                return sprintf("`%s`.%s", $_table, $_filed);
            } else {
                return sprintf("`%s`", $filed);
            }
        }
    }
}

/**
 * 检查数组类型
 *
 * @param array $array
 * @return bool
 */
function is_assoc($array)
{
    return (is_array($array) && (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array) == 0));
}

function get_conn()
{
    $db    =    new db();
    $db->sqlite(DB_PATH, DB_PREFIX);
    return $db;
}

function installed()
{
    $result = false;
    // 能取到安装日期

    $db = @get_conn();
    // 数据库链接不正确
    if (!$db) return $result;
    $tables = array(
        'inquiry', 'user', 'user_meta',
    );
    $table_ok = true;
    // 检查数据表是否正确
    foreach ($tables as $table) {
        if (false === $db->is_table('#@_' . $table)) {
            $table_ok = false;
        }
    }
    $result = $table_ok;

    return $result;
}

/**
 * 创建用户
 *
 * @param string $name
 * @param string $pass
 * @param string $email
 * @param array $data
 * @return array
 */
function user_add($name, $pass, $email, $data = null)
{
    $db = get_conn();
    // 插入用户

    $userid = $db->insert('#@_user', array(
        'username' => $name,
        'password' => $pass,
        'email' => $email,
        'authcode' => '',
        'status' => 0,
        'registered' => date('Y-m-d H:i:s', time()),
    ));
    //$userid = $db->lastinsertid();
    // 生成authcode
    $authcode = authcode($userid);
    $user_info = array(
        'password' => md5($pass . $authcode),
        'authcode' => $authcode,
    );
    if ($data && is_array($data)) {
        $user_info = array_merge($user_info, $data);
    }
    // 更新用户资料
    //print_r($user_info);
    //print_r($userid);
    return user_edit($userid, $user_info);
}

/**
 * 填写用户信息
 *
 * @param int $userid
 * @param array $data
 * @return array|null
 */

function user_edit($userid, $data)
{
    $db = get_conn();
    $userid = intval($userid);
    $user_rows = $meta_rows = array();
    if ($user = user_get_byid($userid)) {
        $data = is_array($data) ? $data : array();
        foreach ($data as $field => $value) {
            // $db->update('#@_user',$user_rows,array('uid' => $userid));
            if ($db->is_field('#@_user', $field)) {
                $user_rows[$field] = $value;
            } else {
                $meta_rows[$field] = $value;
            }
        }
        //print_r($user_rows);
        // 更新数据
        if ($user_rows) {
            $db->update('#@_user', $user_rows, array('uid' => $userid));
        }
        if ($meta_rows) {
            user_edit_meta($userid, $meta_rows);
        }
        // 清理用户缓存
        //user_clean_cache($userid);
        return array_merge($user, $data);
    }
    return null;
}

/**
 * 填写用户扩展信息
 *
 * @param int $userid
 * @param array $data
 * @return bool
 */
function user_edit_meta($userid, $data)
{
    $db = get_conn();
    $userid = intval($userid);
    if (!is_array($data)) return false;
    foreach ($data as $key => $value) {
        // 获取变量类型
        $var_type = gettype($value);
        // 判断是否需要序列化
        $value = is_need_serialize($value) ? serialize($value) : $value;
        // 查询数据库里是否已经存在
        $length = 0;
        if ($length_result = $db->get_results(vsprintf("SELECT * FROM `#@_user_meta` WHERE `uid`=%d AND `key`='%s';", array($userid, esc_sql($key))))) {
            $length = array_values($length_result);
            $length = $length[0];
        }
        // update
        if ($length >= 1) {
            $db->update('#@_user_meta', array(
                'value' => $value,
                'type'  => $var_type,
            ), array(
                'uid' => $userid,
                'key'    => $key,
            ));
        }
        // insert
        else {
            // 保存到数据库里
            $db->insert('#@_user_meta', array(
                'uid' => $userid,
                'key'    => $key,
                'value'  => $value,
                'type'   => $var_type,
            ));
        }
    }
    return true;
}

/**
 * 是否需要序列化
 *
 * @param mixed $value
 * @return bool
 */
function is_need_serialize($value)
{
    return !instr(strtolower(gettype($value)), 'integer,double,string,null');
}
/**
 * 是否需要反序列化
 *
 * @param string $type
 * @return bool
 */
function is_need_unserialize($type)
{
    return !instr(strtolower($type), 'integer,double,string,null');
}
/**
 * 在数组或字符串中查找
 *
 * @param mixed  $needle   需要搜索的字符串
 * @param string|array $haystack 被搜索的数据，字符串用英文"逗号"分割或数组
 * @return bool
 */
function instr($needle, $haystack)
{
    if (empty($haystack)) {
        return false;
    }
    if (!is_array($haystack)) $haystack = explode(',', $haystack);
    return in_array($needle, $haystack) ? true : false;
}

/**
 * 用户登录
 *
 * @param string $username
 * @param string $password
 * @return array $user  用户信息
 *         int   null1   没有此用户
 *         int   0      用户密码不正确
 *         int   负数   用户的其它状态，可能是被锁定
 */
function user_login($username, $password)
{
    if ($user = user_get_byname($username)) {
        if ((int)$user['status'] !== 0) {
            return $user['status'];
        }
        $md5_pass = md5($password . $user['authcode']);
        //$md5_pass = md5(md5($password));

        if ($md5_pass == $user['password']) {
            // 不允许多用户同时登录
            if (
                isset($user['MultiPersonLogin']) === false
                || (isset($user['MultiPersonLogin']) && $user['MultiPersonLogin'] == 'No')
            ) {
                $authcode = authcode($user['uid']);
                if ($authcode != $user['authcode']) {
                    // 生成需要更新的数据
                    $userinfo = array(
                        'password'     => md5($password . $authcode),
                        'authcode' => $authcode,
                    );
                    // 更新数据
                    user_edit($user['uid'], $userinfo);
                    // 合并新密码和key
                    $user = array_merge($user, $userinfo);
                }
            }
            return $user;
        } else {
            // 密码不正确
            return 0;
        }
    } else {
        // 没有此用户
        return null;
    }
}

/**
 * 验证用户是否登录成功
 *
 * @return bool
 */
function user_current($is_redirect = true)
{


    global $_USER;
    $user = null;
    // 取得 authcode
    $authcode = cookie_get('authcode');
    $is_login = $authcode ? true : false;
    // 执行用户验证
    if ($is_login) {
        if ($user = user_get_byauth($authcode)) {
            $is_login = true;
        } else {
            $is_login = false;
        }
    }
    // 未登录，且跳转
    if (!$is_login && $is_redirect) {
        redirect(ROOT . 'login.php');
    }
    $_USER = $user;
    return $user;
}


/**
 * 验证用户权限
 *
 * @param string $action
 * @param bool $is_redirect
 * @return bool
 */
function current_user_can($action, $is_redirect = true)
{
    $result = false;
    $user = user_current(false);
    //print_r($user);
    if (isset($user['Administrator']) && isset($user['roles'])) {
        // 超级管理员
        if ($user['Administrator'] == 'Yes' && $user['roles'] == 'ALL') {
            $result = true;
        }
        // 普通管理员
        elseif ($user['Administrator'] == 'Yes') {
            if (instr($action, (array)$user['roles'])) {
                $result = true;
            }
        }
    }

    // 权限不足
    if (!$result && $is_redirect) {

        global $_USER;
        system_head('title', 'Restricted access');

        //include '/admin/header.php';
        echo error_page('Restricted access', 'Restricted access, please contact the administrator.', true);
        //include '/admin/footer.php';
        exit();
    }
    return $result;
}

/**
 * 通过用户ID查询用户信息
 *
 * @param int $userid
 * @return array|null
 */
function user_get_byid($userid)
{
    $userid = intval($userid);
    return user_get($userid, 0);
}
/**
 * 通过用户名查询用户信息
 *
 * @param string $name
 * @return array|null
 */
function user_get_byname($name)
{
    return user_get($name, 1);
}
/**
 * 通过authcode查询用户信息
 *
 * @param string $authcode
 * @return array|null
 */
function user_get_byauth($authcode)
{
    return user_get($authcode, 2);
}
/**
 * 获取用户的详细信息
 *
 * @param int $userid
 * @return array
 */
function user_get_meta($userid)
{
    $db = get_conn();
    $result = array();
    $userid = intval($userid);
    $rs = $db->query("SELECT * FROM `#@_user_meta` WHERE `uid`={$userid};");
    while ($row = $db->fetch($rs)) {
        if (is_need_unserialize($row['type'])) {
            $result[$row['key']] = unserialize($row['value']);
        } else {
            $result[$row['key']] = $row['value'];
        }
    }
    return $result;
}

/**
 * 取得用户信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function user_get($param, $type = 0)
{
    $db = get_conn();

    //if ($user !== null) return $user;
    switch ($type) {
        case 0:
            $where = sprintf("`uid`=%d", esc_sql($param));
            break;
        case 1:
            $where = sprintf("`username`='%s'", esc_sql($param));
            break;
        case 2:
            $where = sprintf("`authcode`='%s'", esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_user` WHERE {$where} LIMIT 0,1;");
    // 判断用户是否存在
    if ($user = $db->fetch($rs)) {
        if ($meta = user_get_meta($user['uid'])) {
            $user = array_merge($user, $meta);
        }
        // 保存到缓存
        //fcache_set($ckey.$param,$user);
        return $user;
    }

    return null;
}

/**
 * 设置head变量
 *
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function system_head($key, $value = null)
{
    static $head = array();
    // 赋值
    if (!is_null($value)) {
        $head[$key] = $value;
    }
    return isset($head[$key]) ? $head[$key] : array();
}

/**
 * 转义sql语句
 *
 * @param  $str
 * @return string
 */
function esc_sql($str)
{
    $db = get_conn();
    return $db->strescape($str);
}

/**
 * Cookie 管理类
 *
 */
class Cookie
{
    /**
     * 判断cookie是否存在
     *
     * @param string $name
     * @return bool
     */
    function is_set($name)
    {
        return isset($_COOKIE[$name]);
    }
    /**
     * 获取某个cookie值
     *
     * @param string $name
     * @return mixed
     */
    function get($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
    /**
     * 设置某个cookie值
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     */
    function set($name, $value, $expire = 0, $path = '/', $domain = '')
    {
        if (empty($domain)) $domain = COOKIE_DOMAIN;
        if ($expire) $expire = time() + $expire;
        setcookie($name, $value, $expire, $path, $domain);
    }
    /**
     * 删除某个cookie值
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    function delete($name, $path = '/', $domain = '')
    {
        if (empty($domain)) {
            $domain = COOKIE_DOMAIN;
        }
        $this->set($name, '', 1, $path, $domain);
    }
}
/**
 * 实例化对象
 *
 * @return FCache
 */
function &_cookie_get_object()
{
    static $cookie;
    if (is_null($cookie))
        $cookie = new Cookie();
    return $cookie;
}

function cookie_isset($name)
{
    $cookie = _cookie_get_object();
    return $cookie->is_set($name);
}
function cookie_get($name)
{
    $cookie = _cookie_get_object();
    return $cookie->get($name);
}
function cookie_set($name, $value, $expire = 0, $path = '/', $domain = '')
{
    $cookie = _cookie_get_object();
    return $cookie->set($name, $value, $expire, $path, $domain);
}
function cookie_delete($name, $path = '/', $domain = '')
{
    $cookie = _cookie_get_object();
    return $cookie->delete($name, $path, $domain);
}

if (!function_exists('authcode')) :
    /**
     * 给用户生成唯一CODE
     *
     * @param string $data
     * @return string
     */
    function authcode($data = null)
    {
        return guid(HTTP_HOST . $data . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }
endif;
/**
 * 生成guid
 *
 * @param  $randid  字符串
 * @return string   guid
 */
function guid($mix = null)
{
    if (is_null($mix)) {
        $randid = uniqid(mt_rand(), true);
    } else {
        if (is_object($mix) && function_exists('spl_object_hash')) {
            $randid = spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $randid = get_resource_type($mix) . strval($mix);
        } else {
            $randid = serialize($mix);
        }
    }
    $randid = strtoupper(md5($randid));
    $hyphen = chr(45);
    $result = array();
    $result[] = substr($randid, 0, 8);
    $result[] = substr($randid, 8, 4);
    $result[] = substr($randid, 12, 4);
    $result[] = substr($randid, 16, 4);
    $result[] = substr($randid, 20, 12);
    return implode($hyphen, $result);
}


function post_updata($postid, $data)
{
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;
    $data = is_array($data) ? $data : array();
    //$datas = array();

    if ($post = post_get($postid)) {
        // 查询路径
        //$post['path'] = post_get_path($post['sortid'],$post['path']);

        $db->update('#@_inquiry', $data, array('id' => $postid));
        // 清理缓存
        return true;
    }
    return false;
}
/**
 * 删除一片文章
 *
 * @param  $postid
 * @return bool
 */
function post_delete($postid)
{
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;

    if ($post = post_get($postid)) {
        // 查询路径
        //$post['path'] = post_get_path($post['sortid'],$post['path']);

        $db->delete('#@_inquiry', array('id' => $postid));
        // 清理缓存
        return true;
    }
    return false;
}

function trashed_message_auto_clear()
{
    $db = get_conn();
    $db->exec('DELETE FROM #@_inquiry WHERE type="trashed" and time < datetime("now","-30 days")');
    // echo "clear success";
    // exit();
}

function post_trashed($postid, $action = 'trashed')
{
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;

    if ($post = post_get($postid)) {
        // 查询路径
        //$post['path'] = post_get_path($post['sortid'],$post['path']);
        if ($action == 'trashed') {
            $db->update('#@_inquiry', array('type' => 'trashed'), array('id' => $postid));
        }
        if ($action == 'untrashed') {
            $db->update('#@_inquiry', array('type' => 'inquiry'), array('id' => $postid));
        }
        // 清理缓存
        return true;
    }
    return false;
}

function post_mark($postid, $action = 'noread')
{
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;

    if ($post = post_get($postid)) {
        // 查询路径
        //$post['path'] = post_get_path($post['sortid'],$post['path']);
        if ($action == 'noread') {
            $db->update('#@_inquiry', array('read' => '0'), array('id' => $postid));
        }
        if ($action == 'read') {
            $db->update('#@_inquiry', array('read' => '1'), array('id' => $postid));
        }
        // 清理缓存
        return true;
    }
    return false;
}

function replace_message($val)
{

    if (trim($val) == "") {
        return "";
    }

    $val_new = $val;

    if (strpos($val, "@") > 0) {
        $arr_email = explode("@", $val);
        $arr_email[0] = "****";
        $val_new = implode("@", $arr_email);
    } else {
        // $val_new = str_replace($val, str_repeat('X', strlen($val) - 3) . substr($val, -3), $val);
        $repeat_count = strlen($val) - 6;
        $val_new = substr($val, 0, 6) . str_repeat('*', $repeat_count > 0 ? $repeat_count : 0);
    }

    return $val_new;
}

/**
 * 获得指定的留言
 *
 * @param int $postid
 * @return array
 */

function post_get($postid)
{
    $db   = get_conn();

    $rs = $db->query("SELECT * FROM `#@_inquiry` WHERE `id`={$postid} LIMIT 0,1;");

    if ($post = $db->fetch($rs)) {
        if ($meta = post_get_meta($post['id'])) {
            $post['meta'] = $meta;
        }
        //联系信息仅限管理员查看
        if (!current_user_can('inquiry-delete', false)) {
            $post["phone"] = replace_message($post["phone"]);
            $post["email"] = replace_message($post["email"]);
            //$post["ip"] = substr();
        }
        return $post;
    }
    return null;
}
function post_get_meta($inquiryid)
{
    $db = get_conn();
    $result = array();
    $inquiryid = intval($inquiryid);
    $rs = $db->query("SELECT * FROM `#@_inquiry_meta` WHERE `inquiryid`={$inquiryid};");

    while ($row = $db->fetch($rs)) {
        $result[$row['key']] = is_serialized($row['value']) ? unserialize($row['value']) : $row['value'];
    }

    return $result;
}

/**
 * IP地理位置解析
 *
 * @param string $ip
 * @return string
 */
function ip2addr($ip)
{
    static $QQWry;
    if (is_null($QQWry)) {
        //echo ROOT;
        require_once(ABS_PATH . '/includes/qqwry.php');
        $QQWry = new QQWry(ABS_PATH . '/includes/QQWry.Dat');
    }
    return $QQWry->ip2addr($ip);
}

/**
 * 自动转换字符集 支持数组转换
 *
 * @param string $from
 * @param string $to
 * @param mixed  $data
 * @return mixed
 */
function iconvs($from, $to, $data)
{
    $from = strtoupper($from) == 'UTF8' ? 'UTF-8' : $from;
    $to   = strtoupper($to) == 'UTF8' ? 'UTF-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($data) || (is_scalar($data) && !is_string($data))) {
        //如果编码相同或者非字符串标量则不转换
        return $data;
    }
    if (is_string($data)) {
        if (function_exists('iconv')) {
            $to = substr($to, -8) == '//IGNORE' ? $to : $to . '//IGNORE';
            return iconv($from, $to, $data);
        } elseif (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($data, $to, $from);
        } else {
            return $data;
        }
    } elseif (is_array($data)) {
        foreach ($data as $key => $val) {
            $_key        = iconvs($from, $to, $key);
            $data[$_key] = iconvs($from, $to, $val);
            if ($key != $_key) {
                unset($data[$key]);
            }
        }
        return $data;
    } else {
        return $data;
    }
}

function getip()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }

    return $ip;
}

/**
 * 小图标
 *
 * @param string $name
 * @return string
 */
function get_icon($name)
{
    switch ($name) {
        case 'passed':
            $name = 'b8';
            break;
        case 'draft':
            $name = 'b9';
            break;
    }
    return '<img src="' . ROOT . 'admin/images/blank.gif" class="os ' . $name . '" alt="" />';
}

function geturl()
{
    $url_this = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    return $url_this;
}

/**
 * 取得返回地址
 *
 * @param string $default
 * @param bool   $back_server_referer 是否返回来路
 * @return string
 */
function referer($default = '', $back_server_referer = true)
{
    $default = $default ? $default : '/';
    $referer = isset($_REQUEST['referer']) ? $_REQUEST['referer'] : null;
    if ($back_server_referer) {
        if (empty($referer) && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            $referer = esc_html($referer);
        }
    } else {
        if (empty($referer)) {
            $referer = $default;
        } else {
            $referer = esc_html($referer);
        }
    }

    if (strpos($referer, 'login.php') !== false) $referer = $default;
    return $referer;
}

/**
 * 转换特殊字符为HTML实体
 *
 * @param   string $str
 * @return  string
 */
function esc_html($str)
{
    if (empty($str)) {
        return $str;
    } elseif (is_array($str)) {
        $str = array_map('esc_html', $str);
    } elseif (is_object($str)) {
        $vars = get_object_vars($str);
        foreach ($vars as $key => $data) {
            $str->{$key} = esc_html($data);
        }
    } else {
        $str = htmlspecialchars($str);
    }
    return $str;
}

function is_blog_installed()
{
    $db   = get_conn();
    $tables = array_values($db->query("SELECT name FROM sqlite_master where type='table'")->fetchall());
    //$values = array_values( ( $tables ) );
    //print_r($values);

    $new_array = array();
    $wp_tables = array('wp_blogs', 'wp_user');
    $i = 0;
    foreach ($tables as $table) {
        $new_array[$i] = $table['name'];
        $i++;
    }

    foreach ($new_array as $val) {
        if (in_array($val, $wp_tables)) {
            return true;
            continue;
        }
    }



    return false;
}

function inquiry_add($title, $name, $email, $content, $phone, $country, $address, $fromcompany, $metadata = null, $check = true)
{
    $db = get_conn();
    $ua = getBrowser();

    $data = array(
        'blog' => 'http://feedback.love4026.org',
        'user_ip' => getip(),
        'user_agent' => $ua['userAgent'],
        'referrer' => referer(),
        'comment_author' => $name,
        'comment_author_email' => $email,
        'comment_author_url' => '',
        'comment_content' => $content
    );

    $timezone_offset = isset($metadata['timezone_offset']) ? $metadata['timezone_offset'] : null;

    $type = 'inquiry';
    if ($check && akismet_comment_check(Akismet_API_Key, $data)) {
        $type = 'trashed';
    }

    $msgid = $db->insert('#@_inquiry', array(
        'title' => $title,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'content' => $content,
        'time' => date('Y-m-d H:i:s', time()),
        'website' => referer(),
        'ip' => getip(),
        'country' => $country,
        'address' => $address,
        'type'    => $type,
        'from_company'    => $fromcompany,

        'browser_name' => $ua['name'],
        'browser_version' => $ua['version'],
        'browser_platform' => $ua['platform'],
        'user_agent' => $ua['userAgent'],
        'lang' => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        'timezone_offset' => $timezone_offset,


    ));

    return inquiry_edit_meta($msgid, $metadata);
}
function inquiry_edit_meta($inquiryid, $data)
{
    $db = get_conn();
    $inquiryid = intval($inquiryid);
    $data = is_array($data) ? $data : array();
    if (!is_array($data)) return false;
    foreach ($data as $key => $value) {
        // 保存到数据库里
        $db->insert('#@_inquiry_meta', array(
            'inquiryid' => $inquiryid,
            'key'    => $key,
            'value'  => $value,
        ));
    }
    return true;
}

// 验证你的 Akismet API key
function akismet_verify_key($key, $blog)
{
    $blog = urlencode($blog);
    $request = 'key=' . $key . '&blog=' . $blog;
    $host = $http_host = 'rest.akismet.com';
    $path = '/1.1/verify-key';
    $port = 80;
    $akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
    $content_length = strlen($request);
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $http_request .= "Content-Length: {$content_length}\r\n";
    $http_request .= "User-Agent: {$akismet_ua}\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;
    $response = '';
    if (false != ($fs = @fsockopen($http_host, $port, $errno, $errstr, 10))) {

        fwrite($fs, $http_request);

        while (!feof($fs))
            $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);

        $response = explode("\r\n\r\n", $response, 2);
    }

    if ('valid' == $response[1])
        return true;
    else
        return false;
}
function trim_value(&$value)
{
    $value = trim($value);
}
// 对$data进行数据检查，返回 true (垃圾留言) 或者 false (正常留言)
function akismet_comment_check($key, $data)
{
    //先检测ip地址
    $banned_ip = C('banned_ip');
    $banned_ip = explode(',', $banned_ip);
    array_walk($banned_ip, 'trim_value');

    $checkip = isset($data['user_ip']) ? $data['user_ip'] : null;
    if (in_array($checkip, $banned_ip) &&  $checkip != null) {
        return true;
    }

    $request = 'blog=' . urlencode($data['blog']) .
        '&user_ip=' . urlencode($data['user_ip']) .
        '&user_agent=' . urlencode($data['user_agent']) .
        '&referrer=' . urlencode($data['referrer']) .
        '&comment_author=' . urlencode($data['comment_author']) .
        '&comment_author_email=' . urlencode($data['comment_author_email']) .
        '&comment_author_url=' . urlencode($data['comment_author_url']) .
        '&comment_content=' . urlencode($data['comment_content']);
    $host = $http_host = $key . '.rest.akismet.com';
    $path = '/1.1/comment-check';
    $port = 80;
    $akismet_ua = "WordPress/3.1.1 | Akismet/2.5.3";
    $content_length = strlen($request);
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $http_request .= "Content-Length: {$content_length}\r\n";
    $http_request .= "User-Agent: {$akismet_ua}\r\n";
    $http_request .= "\r\n";
    $http_request .= $request;
    $response = '';
    if (false != ($fs = @fsockopen($http_host, $port, $errno, $errstr, 10))) {

        fwrite($fs, $http_request);

        while (!feof($fs))
            $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);

        $response = explode("\r\n\r\n", $response, 2);
    }

    if ('true' == $response[1])
        return true;
    else
        return false;
}

//验证各种输入值
function check()
{
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
                //check($rule);
                if (!check($rule)) break;
            }
        } // Use method 1 rule. 
        else {
            return call_user_func_array('check', $args[0]);
        }
    } else {
        // Validate single rule.
        $error = false;
        $value = isset($_POST[$args[0]]) ? rawurldecode(trim($_POST[$args[0]])) : null; // POST值
        $type  = $args[1]; // 类型
        $text  = $args[2]; // 提示文字
        switch ($type) {
            case 'VALIDATE_EMPTY':
                if (empty($value)) $error = $text;
                break;
            case 'VALIDATE_LENGTH':
                if (!is_numeric($args[3]) && strpos($args[3], '-') !== false) {
                    $as = explode('-', $args[3]);
                    $args[3] = $as[0];
                    $args[4] = $as[1];
                }
                if (
                    mb_strlen($value, 'UTF-8') < (int)$args[3]
                    || mb_strlen($value, 'UTF-8') > (int)$args[4]
                ) {
                    if ($args[3]) {
                        $error = sprintf($text, $args[3], $args[4]);
                    } else {
                        $error = sprintf($text, $args[4]);
                    }
                }
                break;
            case 'VALIDATE_EQUAL':
                $value1 = isset($_POST[$args[3]]) ? trim($_POST[$args[3]]) : null;
                if ($value != $value1) $error = $text;
                break;
            case 'VALIDATE_HAVE_LINK':
                if (strpos($value, 'http') !== false || strpos($value, 'url=') !== false) $error = $text;
                break;
            case false:
            case 'FALSE':
                $error = $text;
                break;
            default:
                if (!is($value, $type)) $error = $text;
                break;
        }

        if (!$error) return true;
        if (is_ajax()) {
            ajax_error(array("code" => 0, "message" => $error));
        } else {
            return Msg($error);
        }
    }
}

/**
 * 静态验证方法
 *
 * @param string $str   需要验证的字符串
 * @param mixed  $type  验证类型，常量或者正则表达式
 * @return bool
 */
function is($str, $type)
{
    switch ($type) {
        case 'IS_NUMERIC':
            $pattern = '/^\d+$/';
            break;
        case 'IS_LETTERS':
            $pattern = '/^[a-z]+$/i';
            break;
        case 'IS_EMAIL':
            $pattern = '/^\w+([\-\+\.]\w+)*@\w+([\-\.]\w+)*\.\w+([\-\.]\w+)*$/i';
            break;
        case 'IS_URL':
            $pattern = '/^(http|https|ftp)\:(\/\/|\\\\)(([\w\/\\\+\-~`@\:%])+\.)+([\w\/\\\.\=\?\+\-~`@\'\:!%#]|(&amp;)|&)+/i';
            break;
        case 'IS_LIST':
            $pattern = '/^[\d\,\.]+$/i';
            break;
        case 'IS_CNUS':
            $pattern = '/^[\w\-]+$/i';
            break;
        case 'IS_CNUSO':
            $pattern = '/^[\w\,\/\-\[\]]+$/i';
            break;
        case 'IS_PATH':
            $pattern = '/^[^\:\*\<\>\|\\\\]+$/';
            break;
        default: // 自定义正则
            $pattern = $type;
            break;
    }
    return preg_match($pattern, $str);
}

/**
 * 页面跳转
 *
 * @param string $url
 * @param int $time
 * @param string $msg
 * @return void
 */
function redirect($url, $time = 0, $msg = '')
{
    // 多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg)) $msg = sprintf('<a href="%s">%d seconds after goto %s.</a>', $url, $time, $url);
    if (!headers_sent()) header("Content-Type:text/html; charset=utf-8");

    if (!headers_sent()) {
        if (0 === intval($time)) {
            header("Location: {$url}");
        }
    }


    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    $html .= '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $html .= '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />';
    $html .= '<title>Redirecting...</title>';
    $html .= '<script type="text/javascript" charset="utf-8">';
    $html .= 'window.setTimeout(function(){location.replace("' . $url . '");}, ' . ($time * 1000) . ');';
    $html .= '</script>';
    $html .= '</head><body>';
    $html .= 0 === $time ? null : $msg;
    $html .= '</body></html>';
    exit($html);
}

if (!function_exists('error_page')) :
    /**
     * 错误页面
     *
     * @param string $title
     * @param string $content
     * @param bool $is_full     是否输出完整页面
     * @return string
     */
    function error_page($title, $content, $is_full = false)
    {
        // CSS
        $css = '<style type="text/css">';
        $css .= '#error-page { width:600px; min-height:250px; background:#fff url(common/images/warning-large.png) no-repeat 15px 10px; margin-top:15px; padding-bottom:30px; border:1px solid #B5B5B5; }';
        $css .= '#error-page { -moz-border-radius:6px; -webkit-border-radius:6px; -khtml-border-radius:6px; border-radius:6px; }';
        $css .= '#error-title { width:500px; border-bottom:solid 1px #B5B5B5; margin:0 0 15px 80px; }';
        $css .= '#error-title h1{ font-size: 25px; margin:10px 0 5px 0; }';
        $css .= '#error-content,#error-buttons { margin:10px 0 10px 80px; }';
        if ($is_full) {
            $css .= 'body { margin:10px 20px; font-family: Verdana; color: #333333; background:#FAFAFA; font-size: 12px; line-height: 1.5; }';
            $css .= '#error-page { width:900px; margin:15px auto; }';
            $css .= '#error-title { width:800px;}';
        }
        $css .= '</style>';
        // Page
        $page = '<div id="error-page">';
        $page .= '<div id="error-title"><h1>' . $title . '</h1></div>';
        $page .= '<div id="error-content">' . $content . '</div>';
        $page .= '<div id="error-buttons"><button type="button" onclick="window.history.back();">Back</button></div>';
        $page .= '</div>';

        if ($is_full) {
            $hl = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
            $hl .= '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $hl .= '<title>' . $title . ' &#8212; Inuiry System</title>';
            $hl .= $css . '</head><body>' . $page;
            $hl .= '</body></html>';
        } else {
            $hl = $css . $page;
        }
        return $hl;
    }
endif;


/**
 * 显示系统信息
 *
 * @param string $msg 信息
 * @param string $url 返回地址
 * @param boolean $isAutoGo 是否自动返回 true false
 */
function Msg($msg, $url = 'javascript:history.back(-1);', $isAutoGo = false)
{
    if ($msg == '404') {
        header("HTTP/1.1 404 Not Found");
        $msg = 'Sorry, The page you request is not found !';
    }
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
EOT;
    if ($isAutoGo) {
        echo "<meta http-equiv=\"refresh\" content=\"2;url=$url\" />";
    }
    echo <<<EOT
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>system message</title>
<style type="text/css">
<!--
body {
	background-color:#F7F7F7;
	font-family: Arial;
	font-size: 12px;
	line-height:150%;
	color:#333;
}
.main {
	background-color:#FFFFFF;
	font-size: 12px;

	width:750px;
	margin:100px auto;
	border-radius: 10px;
	padding:30px 32px;
	list-style:none;
	border:#DFDFDF 1px solid;
}
.main p {
	line-height: 18px;
	padding-bottom: 2px;
}
h1{border-bottom:1px solid #dadada;clear:both;color:#666;font:24px Georgia,"Times New Roman",Times,serif;margin:5px 0 0 -4px;padding:0;padding-bottom:7px;}
.step{margin:20px 0 15px;}.step,th{text-align:left;padding:0;}.submit input,.button,.button-secondary{font-family:sans-serif;text-decoration:none;font-size:14px!important;line-height:16px;padding:6px 12px;cursor:pointer;border:1px solid #bbb;color:#464646;-moz-border-radius:15px;-khtml-border-radius:15px;-webkit-border-radius:15px;border-radius:15px;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;-khtml-box-sizing:content-box;box-sizing:content-box;}.button:hover,.button-secondary:hover,.submit input:hover{color:#000;border-color:#666;}.button,.submit input,.button-secondary{background:#f2f2f2 url(/admin/images/white-grad.png) repeat-x scroll left top;}.button:active,.submit input:active,.button-secondary:active{background:#eee url(/admin/images/white-grad-active.png) repeat-x scroll left top;}.message{border:1px solid #e6db55;padding:.3em .6em;margin:5px 0 15px;background-color:#ffffe0;}
-->
</style>
</head>
<body>
<div class="main ">
<p>$msg</p>
<p class="step"><a href="$url" class="button">&laquo; Back</a></p>
</div>
</body>
</html>
EOT;
    exit;
}


/**
 * 检查状态
 *
 * @param bool $state
 * @return string
 */
function test_result($state)
{
    return $state ? '<strong style="color:#009900;">&radic;</strong>' : '<strong style="color:#FF0000;">&times;</strong>';
}


/**
 * 统计Post数量
 *
 * @param string $type
 * @return int
 */
function post_count($type)
{
    switch ($type) {
        case 'read':
            $val = '`read` = 1  AND `type`="inquiry"';
            break;
        case 'noread':
            $val = '`read` = 0  AND `type`="inquiry"';
            break;
        case 'trash':
            $val = '`type`="trashed"';
            break;
        default:
            $val = '`type` = "inquiry"';
            break;
    }
    $db = get_conn();
    $num    =    array_values($db->get_results(sprintf("SELECT COUNT(`id`) FROM `#@_inquiry` WHERE %s", $val)));
    return $num[0];
}

/**
 * 清除空白
 *
 * @param  $content
 * @return mixed
 */
function clear_space($content)
{
    if (strlen($content) == 0) return $content;
    $r = $content;
    $r = str_replace(array(chr(9), chr(10), chr(13)), '', $r);
    while (strpos($r, chr(32) . chr(32)) !== false || strpos($r, '&nbsp;') !== false) {
        $r = str_replace(chr(32) . chr(32), chr(32), str_replace('&nbsp;', chr(32), $r));
    }
    return $r;
}


/**
 * 分页类
 *
 */
class Pages
{
    var $_db = null;
    var $total, $pages, $page, $size, $length;

    /*    function Pages() {
        $args = func_get_args();
		call_user_func_array( array(&$this, '__construct'), $args );
	}*/
    /**
     * 初始化
     *
     * 接收 page,size 变量
     *
     * @return void
     */
    function __construct($size = null, $page = null)
    {
        $this->size = $size === null ? 10 : $size;

        if ($page === null) {
            $this->page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        } else {
            $this->page = $page;
        }

        $this->page = $this->page < 1 ? $page : $this->page;
        $this->size = $this->size < 1 ? $size : $this->size;
        $this->_db  = get_conn();
    }
    /**
     * 执行查询
     *
     * @param string $sql
     * @return mixed
     */
    function query($sql)
    {
        /*
        // 如果提示 Deprecated: Function create_function() is deprecated in， 按下面提示修改
        function($matches) use ($field) {
            if (preg_match('/DISTINCT\s*\([^\)]+\)/i', $matches[1], $match)) {
                $field = $match[0];
            } else {
                $field = "*";
            }
            return sprintf("SELECT COUNT(%s) FROM", $field);
        }
        */
        $csql = preg_replace_callback(
            '/SELECT (.+) FROM/iU',
            function ($matches) {
                if (preg_match('/DISTINCT\s*\([^\)]+\)/i', $matches[1], $match)) {
                    $field = $match[0];
                } else {
                    $field = "*";
                }
                return sprintf("SELECT COUNT(%s) FROM", $field);
            },
            rtrim($sql, ';'),
            1
        );
        $csql = preg_replace('/\sORDER\s+BY.+$/i', '', $csql, 1);

        $sql .= sprintf(' LIMIT %d OFFSET %d;', $this->size, ($this->page - 1) * $this->size);
        // 执行结果
        $result = $this->_db->query($sql);
        // 总记录数
        $this->total  = $this->_db->rowcount($csql);
        $this->pages  = ceil($this->total / $this->size);
        $this->pages  = ((int)$this->pages == 0) ? 1 : $this->pages;
        if ((int)$this->page < (int)$this->pages) {
            $this->length = $this->size;
        } elseif ((int)$this->page == (int)$this->pages) {
            $this->length = $this->total - (($this->pages - 1) * $this->size);
        } else {
            $this->length = 0;
        }
        if ($this->total == 0 || $this->length == 0) $result = false;
        return $result;
    }
    /**
     * 取得数据集
     *
     * @param  $resource
     * @param int $type
     * @return array|null
     */
    function fetch($resource, $type = 1)
    {
        if (is_resource($resource) || is_object($resource)) {
            return $this->_db->fetch($resource, $type);
        }
    }
    /**
     * 分页信息
     *
     * @return array
     */
    function info()
    {
        return array(
            'page'   => $this->page,
            'size'   => $this->size,
            'total'  => $this->total,
            'pages'  => $this->pages,
            'length' => $this->length,
        );
    }
    /**
     * 清理分页信息
     *
     * @return void
     */
    function close()
    {
        $this->total = $this->pages = $this->length = 0;
    }

    /**
     * 分页函数
     *
     * @param string $url   url中必须包含$特殊字符，用来代替页数
     * @param string $mode  首页丢弃模式
     * @return string
     */
    function page_list($url, $mode = '$')
    {
        // print_r($url);
        $html = '';
        $this->page   = abs(intval($this->page));
        $this->page   = $this->page < 1 ? 1  : $this->page;
        $this->pages  = abs(intval($this->pages));
        $this->length = abs(intval($this->length));
        if (strpos($url, '%24') !== false)
            $url = str_replace('%24', '$', $url);
        if (strpos($url, '$') === false || $this->length == 0)
            return;

        $start = instr($mode, '!$,!_$') ? '' : 1;
        if ($this->page > 2) {
            $html .= '<a href="' . str_replace('$', $this->page - 1, $url) . '">&laquo;</a>';
        } elseif ($this->page == 2) {
            if ($mode == '!_$') {
                $html .= '<a href="' . str_replace('_$', $start, $url) . '">&laquo;</a>';
            } else {
                $html .= '<a href="' . str_replace('$', $start, $url) . '">&laquo;</a>';
            }
        }
        if ($this->page > 3) {
            if ($mode == '!_$') {
                $html .= '<a href="' . str_replace('_$', $start, $url) . '">1</a><span>&#8230;</span>';
            } else {
                $html .= '<a href="' . str_replace('$', $start, $url) . '">1</a><span>&#8230;</span>';
            }
        }
        $before = $this->page - 2;
        $after  = $this->page + 7;
        for ($i = $before; $i <= $after; $i++) {
            if ($i >= 1 && $i <= $this->pages) {
                if ((int)$i == (int)$this->page) {
                    $html .= '<span class="active">' . $i . '</span>';
                } else {
                    if ($i == 1) {
                        if ($mode == '!_$') {
                            $html .= '<a href="' . str_replace('_$', $start, $url) . '">' . $i . '</a>';
                        } else {
                            $html .= '<a href="' . str_replace('$', $start, $url) . '">' . $i . '</a>';
                        }
                    } else {
                        $html .= '<a href="' . str_replace('$', $i, $url) . '">' . $i . '</a>';
                    }
                }
            }
        }
        if ($this->page < ($this->pages - 7)) {
            $html .= '<span>&#8230;</span><a href="' . str_replace('$', $this->pages, $url) . '">' . $this->pages . '</a>';
        }
        if ($this->page < $this->pages) {
            $html .= '<a href="' . str_replace('$', $this->page + 1, $url) . '">&raquo;</a>';
        }
        return '<div class="pages">' . $html . '</div>';
    }
}

/**
 * 分页实例
 *
 * @return Pages
 */
function &_pages_get_object($size = null, $page = null)
{
    static $pages;
    if (is_null($pages))
        $pages = new Pages($size, $page);
    return $pages;
}
/**
 * 初始化分页类
 *
 * @param int $size
 * @param int $page
 * @return Pages
 */
function pages_init($size = 10, $page = null)
{
    return _pages_get_object($size, $page);
}
/**
 * 执行分页查询
 *
 * @param string $sql
 * @return mixed
 */
function pages_query($sql)
{
    $pages = _pages_get_object();
    return $pages->query($sql);
}
/**
 * 取得数据集
 *
 * @param resource $resource
 * @param int $type
 * @return array|null
 */
function pages_fetch($resource, $type = 1)
{
    $pages = _pages_get_object();
    return $pages->fetch($resource, $type);
}

if (!function_exists('pages_list')) :
    /**
     * 分页列表
     *
     * @param string $url   $ 代表当前页数
     * @param string $mode  首页丢弃模式
     * @param int $page     当前页数
     * @param int $total    总页数
     * @param int $length   当前页记录数
     * @return string
     */
    function pages_list($url, $mode = '$', $page = null, $total = null, $length = null)
    {
        $pages = _pages_get_object();
        if ($page !== null)   $pages->page   = $page;
        if ($total !== null)  $pages->pages  = $total;
        if ($length !== null) $pages->length = $length;
        return $pages->page_list($url, $mode);
    }
endif;
/**
 * 分页信息
 *
 * @return array
 */
function pages_info()
{
    $pages = _pages_get_object();
    return $pages->info();
}
/**
 * 清理分页信息
 *
 * @return void
 */
function pages_close()
{
    $pages = _pages_get_object();
    return $pages->close();
}


function alert_echo($string, $url = null)
{
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

    // 跳转用history.back，不再使用referer了
    if (empty($url)) {
        $redirect_js = 'history.back();';
    } else {
        $redirect_js = 'window.location.href="' . $url . '"';
    }

    // 如果设置了SUBMIT_DONE_REDIRECT_JS，强制使用自定义的
    if(defined('SUBMIT_DONE_REDIRECT_JS') && !empty(SUBMIT_DONE_REDIRECT_JS)) {
        $redirect_js = SUBMIT_DONE_REDIRECT_JS;
    }

    echo '<script type="text/javascript">alert("' . $string . '");' . $redirect_js . '</script>';
    ob_flush();
    exit();
}

/**
 * 判断是否为ajax提交
 *
 * @return bool
 */
function is_ajax()
{
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null) == 'XMLHttpRequest';
}
/**
 * 输出ajax规范的json字符串
 *
 * @param  $code
 * @param  $data
 * @param  $eval
 * @return void
 */
function ajax_echo($code, $data, $eval = null)
{
    if (!headers_sent()) {
        if ($code) header('X-RAY-Code: ' . $code);
        if ($eval) header('X-RAY-Eval: ' . $eval);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data);
    ob_flush();
    exit();
}
function ajax_alert($message, $eval = null)
{
    return ajax_echo('Alert', $message, $eval);
}
function ajax_success($message, $eval = null)
{
    return ajax_echo('Success', $message, $eval);
}
function ajax_error($message, $eval = null)
{
    return ajax_echo('Error', $message, $eval);
}
function ajax_return($data)
{
    return ajax_echo('Return', $data);
}

/**
 * Retrieve a modified URL query string.
 *
 * You can rebuild the URL and append a new query variable to the URL query by
 * using this function. You can also retrieve the full URL with query data.
 *
 * Adding a single key & value or an associative array. Setting a key value to
 * an empty string removes the key. Omitting oldquery_or_uri uses the $_SERVER
 * value. Additional values provided are expected to be encoded appropriately
 * with urlencode() or rawurlencode().
 *
 * @since 1.5.0
 *
 * @param mixed $param1 Either newkey or an associative_array
 * @param mixed $param2 Either newvalue or oldquery or uri
 * @param mixed $param3 Optional. Old query or uri
 * @return string New URL query string.
 */
function add_query_arg()
{
    $ret = '';
    if (is_array(func_get_arg(0))) {
        if (@func_num_args() < 2 || false === @func_get_arg(1))
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = @func_get_arg(1);
    } else {
        if (@func_num_args() < 3 || false === @func_get_arg(2))
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = @func_get_arg(2);
    }

    if ($frag = strstr($uri, '#'))
        $uri = substr($uri, 0, -strlen($frag));
    else
        $frag = '';

    if (preg_match('|^https?://|i', $uri, $matches)) {
        $protocol = $matches[0];
        $uri = substr($uri, strlen($protocol));
    } else {
        $protocol = '';
    }

    if (strpos($uri, '?') !== false) {
        $parts = explode('?', $uri, 2);
        if (1 == count($parts)) {
            $base = '?';
            $query = $parts[0];
        } else {
            $base = $parts[0] . '?';
            $query = $parts[1];
        }
    } elseif (!empty($protocol) || strpos($uri, '=') === false) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    wp_parse_str($query, $qs);
    $qs = urlencode_deep($qs); // this re-URL-encodes things that were already in the query string
    if (is_array(func_get_arg(0))) {
        $kayvees = func_get_arg(0);
        $qs = array_merge($qs, $kayvees);
    } else {
        $qs[func_get_arg(0)] = func_get_arg(1);
    }

    foreach ((array) $qs as $k => $v) {
        if ($v === false)
            unset($qs[$k]);
    }

    $ret = build_query($qs);
    $ret = trim($ret, '?');
    $ret = preg_replace('#=(&|$)#', '$1', $ret);
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim($ret, '?');
    return $ret;
}

/**
 * Removes an item or list from the query string.
 *
 * @since 1.5.0
 *
 * @param string|array $key Query key or keys to remove.
 * @param bool $query When false uses the $_SERVER value.
 * @return string New URL query string.
 */
function remove_query_arg($key, $query = false)
{
    if (is_array($key)) { // removing multiple keys
        foreach ($key as $k)
            $query = add_query_arg($k, false, $query);
        return $query;
    }
    return add_query_arg($key, false, $query);
}

/**
 * Retrieve referer from '_wp_http_referer' or HTTP referer. If it's the same
 * as the current request URL, will return false.
 *
 * @package WordPress
 * @subpackage Security
 * @since 2.0.4
 *
 * @return string|bool False on failure. Referer URL on success.
 */
function get_referer()
{
    $ref = false;
    if (!empty($_REQUEST['_http_referer']))
        $ref = $_REQUEST['_http_referer'];
    else if (!empty($_SERVER['HTTP_REFERER']))
        $ref = $_SERVER['HTTP_REFERER'];

    if ($ref && $ref !== $_SERVER['REQUEST_URI'])
        return $ref;
    return false;
}

function wp_parse_str($string, &$array)
{
    parse_str($string, $array);
    if (get_magic_quotes_gpc())
        $array = stripslashes_deep($array);
    //$array = apply_filters( 'wp_parse_str', $array );
}
/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @since 2.0.0
 *
 * @param array|string $value The array or string to be stripped.
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value)
{
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } else {
        $value = stripslashes($value);
    }

    return $value;
}
function urlencode_deep($value)
{
    $value = is_array($value) ? array_map('urlencode_deep', $value) : urlencode($value);
    return $value;
}
function build_query($data)
{
    return _http_build_query($data, null, '&', '', false);
}
// from php.net (modified by Mark Jaquith to behave like the native PHP5 function)
function _http_build_query($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
{
    $ret = array();

    foreach ((array) $data as $k => $v) {
        if ($urlencode)
            $k = urlencode($k);
        if (is_int($k) && $prefix != null)
            $k = $prefix . $k;
        if (!empty($key))
            $k = $key . '%5B' . $k . '%5D';
        if ($v === NULL)
            continue;
        elseif ($v === FALSE)
            $v = '0';

        if (is_array($v) || is_object($v))
            array_push($ret, _http_build_query($v, '', $sep, $k, $urlencode));
        elseif ($urlencode)
            array_push($ret, $k . '=' . urlencode($v));
        else
            array_push($ret, $k . '=' . $v);
    }

    if (NULL === $sep)
        $sep = ini_get('arg_separator.output');

    return implode($sep, $ret);
}
function absint($maybeint)
{
    return abs(intval($maybeint));
}

/**
 * 检查值是否已经序列化
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool
 */
function is_serialized($data)
{
    // if it isn't a string, it isn't serialized
    if (!is_string($data))
        return false;
    $data = trim($data);
    if ('N;' == $data)
        return true;
    if (!preg_match('/^([adObis]):/', $data, $badions))
        return false;
    switch ($badions[1]) {
        case 'a':
        case 'O':
        case 's':
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b':
        case 'i':
        case 'd':
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}

function C($key, $value = null)
{
    // 批量赋值
    if (is_array($key)) {
        foreach ($key as $k => $v) {
            C($k, $v);
        }
        return true;
    }

    $db  = @get_conn();
    // 取值
    if ($key && func_num_args() == 1) {
        // 数据库链接有问题
        //if ($db && !$db->ready) return null;

        if ($db->is_table('#@_option')) {
            $result = $db->query(sprintf("SELECT `option_value` FROM `#@_option` WHERE `option_name`='%s' LIMIT 1 OFFSET 0;", $key));

            if ($data = $db->fetch($result)) {
                $value = is_serialized($data['option_value']) ? unserialize($data['option_value']) : $data['option_value'];
            }
        }

        return $value;
    }
    // 参数赋值
    else {
        // 删除属性
        if (is_null($value)) {
            $db->delete('#@_option', array(
                'option_name' => $key
            ));
        } else {
            // 查询数据库里是否已经存在
            $length = (int) $db->result(sprintf("SELECT COUNT(`option_id`) FROM `#@_option` WHERE `option_name`='%s'", esc_sql($key)));
            // update
            if ($length > 0) {
                $db->update('#@_option', array(
                    'option_value' => $value,
                ), array(
                    'option_name' => $key
                ));
            }
            // insert
            else {
                // 保存到数据库里
                $db->insert('#@_option', array(
                    'option_name' => $key,
                    'option_value' => $value
                ));
            }
        }
        return true;
    }
    return null;
}



function getBrowser()
{
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }

    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "") {
        $version = "?";
    }

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 
