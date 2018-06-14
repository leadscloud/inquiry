<?php
set_time_limit(0);
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
current_user_can('export');
$_USER = user_current();
system_head('title','导入');

$file = array();
if ( isset( $_POST['action'] ) ) {
	if ( !isset($_FILES['import']) ) {
			$file['error'] = '文件是空的。';
	}else if($_FILES["import"]["error"] > 0){
			die("文件上传发生错误：".$_FILES["import"]["error"]);
	}
	
	$xml = new SofeeXmlParser();
	$xml->parseFile($_FILES["import"]["tmp_name"]);
	$tree = $xml->getTree();
	unset($xml);
	$db = get_conn();

	$post_data = $tree['rss']['channel']['item'];
	if(!isset($tree['rss']['channel']['item'][0])){
		//print_r($tree['rss']['channel']['item']);
		foreach($tree['rss']['channel']['item'] as $key=>$value){
			 $post_data2[0][$key]=$value;
		}
		$post_data =$post_data2;
		//print_r($post_data);
	}
	
	foreach ( $post_data as $tree_arr ) {
		$data = array(
       		'title' => $tree_arr['title']['value'],
       		'name' => $tree_arr['name']['value'],
	   		'email' => $tree_arr['email']['value'],
       		'phone' => $tree_arr['phone']['value'],
       		'content' => $tree_arr['content:encoded']['value'],
       		'time' => date('Y-m-d H:i:s',strtotime($tree_arr['date']['value'])),
	   		'website' => $tree_arr['website']['value'],
	   		'ip' => $tree_arr['ip']['value'],
	   		'country' => $tree_arr['country']['value'],
	   		'address' => $tree_arr['address']['value'],
			'read'	=>	$tree_arr['read']['value'],
			'status'	=>	$tree_arr['status']['value'],
	   		'type'	=> $tree_arr['type']['value']
    	);
		$db->insert('_inquiry',$data);
	}
}

$bytes = max_upload_size();
$size = convert_bytes_to_hr( $bytes );
	
include 'header.php';
 		echo '<div class="wrap">';
        echo   '<h2>导入</h2>';
		echo   '<div class="clear"></div>';
		echo	'<p>选择xml文件导入.</p>';
		//if(isset($file['error'])&&$file['error']!="") echo '<p>'.$file['error'].'</p>';
		echo	'<form enctype="multipart/form-data" id="import-upload-form" method="post" action="">';
		echo	'<p>';
		echo	'<label for="upload">从你的电脑中选择一个文件:</label> (最大值: '.$size.')';
		echo	'<input type="file" id="upload" name="import" size="25">';
		echo	'<input type="hidden" name="action" value="save">';
		echo	'<input type="hidden" name="max_file_size" value="'.$bytes.'">';
		echo	'</p>';
		echo	'<p class="submit"><input type="submit" name="submit" id="submit" class="button-secondary" value="上传文件并导入"></p>';
		echo	'</form>';
		echo '</div>';
include 'footer.php';

function convert_bytes_to_hr( $bytes ) {
	$units = array( 0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB' );
	$log = log( $bytes, 1024 );
	$power = (int) $log;
	$size = pow(1024, $log - $power);
	return $size . $units[$power];
}
function convert_hr_to_bytes( $size ) {
	$size = strtolower($size);
	$bytes = (int) $size;
	if ( strpos($size, 'k') !== false )
		$bytes = intval($size) * 1024;
	elseif ( strpos($size, 'm') !== false )
		$bytes = intval($size) * 1024 * 1024;
	elseif ( strpos($size, 'g') !== false )
		$bytes = intval($size) * 1024 * 1024 * 1024;
	return $bytes;
}

function max_upload_size() {
	$u_bytes = convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
	$p_bytes = convert_hr_to_bytes( ini_get( 'post_max_size' ) );
	$bytes	=	min($u_bytes, $p_bytes);
	return $bytes;
}

class SofeeXmlParser
{
    var $parser;
    var $srcenc;
    var $dstenc;
    var $_struct = array();

    function SofeeXmlParser($srcenc = null, $dstenc = null)
    {
        $this->srcenc = $srcenc;
        $this->dstenc = $dstenc;
        $this->parser = null;
        $this->_struct = array();
    }
    function free()
    {
        if (isset($this->parser) && is_resource($this->parser))
        {
            xml_parser_free($this->parser);
            unset($this->parser);
        }
    }
    function parseFile($file)
    {
        $data = @file_get_contents($file) or die("Can't open file $file for reading!");
        $this->parseString($data);
    }
    function parseString($data)
    {
        if ($this->srcenc === null)
        {
            $this->parser = @xml_parser_create() or die('Unable to create XML parser resource.');
        }
        else
        {
            $this->parser = @xml_parser_create($this->srcenc) or die('Unable to create XML parser resource with ' . $this->srcenc . ' encoding.');
        }

        if ($this->dstenc !== null)
        {
            @xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->dstenc) or die('Invalid target encoding');
        }
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0); // lowercase tags
        xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1); // skip empty tags
        if (@!xml_parse_into_struct($this->parser, $data, $this->_struct))
        {
            printf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->parser)),
                xml_get_current_line_number($this->parser)
                );
            $this->free();
            exit();
        }

        $this->_count = count($this->_struct);
        $this->free();
    }
    function getTree()
    {
        $i = 0;
        $tree = array();

        $tree = $this->addNode($tree,
            $this->_struct[$i]['tag'],
            (isset($this->_struct[$i]['value'])) ? $this->_struct[$i]['value'] : '',
            (isset($this->_struct[$i]['attributes'])) ? $this->_struct[$i]['attributes'] : '',
            $this->getChild($i)
            );

        unset($this->_struct);
        return ($tree);
    }

    function getChild(&$i)
    {
        // contain node data
        $children = array();
        // loop
        while (++$i < $this->_count)
        {
            // node tag name
            $tagname = $this->_struct[$i]['tag'];
            $value = isset($this->_struct[$i]['value']) ? $this->_struct[$i]['value'] : '';
            $attributes = isset($this->_struct[$i]['attributes']) ? $this->_struct[$i]['attributes'] : '';

            switch ($this->_struct[$i]['type'])
            {
                case 'open':
                    // node has more children
                    $child = $this->getChild($i);
                    // append the children data to the current node
                    $children = $this->addNode($children, $tagname, $value, $attributes, $child);
                    break;
                case 'complete':
                    // at end of current branch
                    $children = $this->addNode($children, $tagname, $value, $attributes);
                    break;
                case 'cdata':
                    // node has CDATA after one of it's children
                    $children['value'] .= $value;
                    break;
                case 'close':
                    // end of node, return collected data
                    return $children;
                    break;
            }
        }
        // return $children;
    }
    function addNode($target, $key, $value = '', $attributes = '', $child = '')
    {
        if (!isset($target[$key]['value']) && !isset($target[$key][0]))
        {
            if ($child != '')
            {
                $target[$key] = $child;
            }
            if ($attributes != '')
            {
                foreach ($attributes as $k => $v)
                {
                    $target[$key][$k] = $v;
                }
            }

            $target[$key]['value'] = $value;
        }
        else
        {
            if (!isset($target[$key][0]))
            {
                // is string or other
                $oldvalue = $target[$key];
                $target[$key] = array();
                $target[$key][0] = $oldvalue;
                $index = 1;
            }
            else
            {
                // is array
                $index = count($target[$key]);
            }

            if ($child != '')
            {
                $target[$key][$index] = $child;
            }

            if ($attributes != '')
            {
                foreach ($attributes as $k => $v)
                {
                    $target[$key][$index][$k] = $v;
                }
            }
            $target[$key][$index]['value'] = $value;
        }
        return $target;
    }
}
?>