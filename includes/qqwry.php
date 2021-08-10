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

class QQWry {
    var $_fp = null;
    var $_start = 0, $_end = 0, $_ctype = 0;
    var $_fstart = 0, $_lstart = 0, $_offset = 0;

    /*function QQWry() {
        register_shutdown_function( array(&$this, '__destruct') );
        
        $args = func_get_args();
		call_user_func_array( array(&$this, '__construct'), $args );
	}*/

    function __construct($dat_file) {
        if (is_file($dat_file)) {
            $this->_fp = fopen($dat_file,'rb');
        }
    }

    function _init() {
        $this->_start = $this->_end = $this->_ctype = $this->_fstart = $this->_lstart = $this->_offset = 0;
    }

    function _start($number) {
        fseek($this->_fp, $this->_fstart + $number * 7);
        $buf = fread($this->_fp, 7);
        $this->_offset = ord($buf[4]) + (ord($buf[5]) * 256) + (ord($buf[6]) * 256 * 256);
        $this->_start  = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
        return $this->_start;
    }

    function _end() {
        fseek($this->_fp, $this->_offset);
        $buf = fread($this->_fp, 5);
        $this->_end   = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
        $this->_ctype = ord($buf[4]);
        return $this->_end;
    }

    function _get_addr() {
        $result = array();
        switch ($this->_ctype) {
            case 1:
            case 2:
                $result['Country'] = $this->_get_str($this->_offset + 4);
                $result['Local']   = (1 == $this->_ctype) ? '' : $this->_get_str($this->_offset + 8);
                break;
            default :
                $result['Country'] = $this->_get_str($this->_offset + 4);
                $result['Local']   = $this->_get_str(ftell($this->_fp));
        }
        return $result;
    }

    function _get_str($offset) {
        $flag = 0; $result = '';
        while (true) {
            fseek($this->_fp, $offset);
            $flag = ord(fgetc($this->_fp));
            if ($flag == 1 || $flag == 2) {
                $buf = fread($this->_fp, 3);
                if ($flag == 2) {
                    $this->_ctype  = 2;
                    $this->_offset = $offset - 4;
                }
                $offset = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256);
            } else {
                break;
            }

        }
        if ($offset < 12) return $result;
        fseek($this->_fp, $offset);
        while (true) {
            $c = fgetc($this->_fp);
            if (ord($c[0]) == 0) break;    
            $result.= $c;
        }
        return $result;
    }

    function ip2addr($ipaddr) {
        if (!$this->_fp) return $ipaddr;
        if (strpos($ipaddr, '.') !== false) {
            if (preg_match('/^(127)/', $ipaddr))
                return 'Local';
            $ip = sprintf('%u',ip2long($ipaddr));
        } else {
            $ip = $ipaddr;
        }
        $this->_init();
        fseek($this->_fp, 0);
        $buf = fread($this->_fp, 8);
        $this->_fstart = ord($buf[0]) + (ord($buf[1])*256) + (ord($buf[2])*256*256) + (ord($buf[3])*256*256*256);
	    $this->_lstart = ord($buf[4]) + (ord($buf[5])*256) + (ord($buf[6])*256*256) + (ord($buf[7])*256*256*256);

        $count = floor(($this->_lstart - $this->_fstart) / 7);
        if ($count <= 1) {
            fclose($this->_fp);
            return $ipaddr;
        }

        $start = 0;
        $end = $count;
        while ($start < $end - 1)
        {
            $number = floor(($start + $end) / 2);
            $this->_start($number);

            if ($ip == $this->_start) {
                $start = $number;
                break;
            }
            if ($ip > $this->_start)
                $start = $number;
            else
                $end = $number;
        }
        $this->_start($start);
        $this->_end();

        if (($this->_start <= $ip) && ($this->_end >= $ip)) {
            $result = $this->_get_addr();
        } else {
            $result = array(
                'Country' => 'Unknown',
                'Local'   => '',
            );
        }
        $result = trim(implode(' ', $result));
        $result = iconvs('GBK', 'UTF-8', $result);
        return $result;
    }
    
    function __destruct() {
        if ($this->_fp) fclose($this->_fp);
    }
}