<?php
define( 'WXR_VERSION', '1.1' );
require_once '../defines.php';
require_once '../includes/lib/function.base.php';
current_user_can('export');
$_USER = user_current();
system_head('title','导出');

if ( isset( $_GET['download'] ) ) {
	export_xml(  );
	die();
}

function export_xml( $args = array() ) {
	
	$defaults = array( 'content' => 'all', 'author' => false, 'category' => false,
		'start_date' => false, 'end_date' => false, 'status' => false,
	);
	
	$args = wp_parse_args( $args, $defaults );
	$filename = 'inqiury.' . date( 'Y-m-d' ) . '.xml';
	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Type: text/xml; charset=utf-8', true );
	
	$db = get_conn();
	
	$stmt = $db->query( "SELECT * FROM #@_inquiry" );
	
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
>

<channel>
	<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
	<wxr_version><?php echo WXR_VERSION; ?></wxr_version>
    
<?php
	while ($post = $db->fetch($stmt,1)) {
		//print_r($post);
?>
	<item>
		<title><?php echo wxr_cdata( $post['title'] ); ?></title>
		<name><?php echo wxr_cdata( $post['name'] ); ?></name>
        <phone><?php echo wxr_cdata( $post['phone'] ); ?></phone>
		<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', $post['time']); ?></pubDate>
        <date><?php echo  $post['time'] ; ?></date>
		<description></description>
		<content:encoded><?php echo wxr_cdata( $post['content'] ); ?></content:encoded>
		<inqiury_id><?php echo $post['id']; ?></inqiury_id>
		<email><?php echo wxr_cdata($post['email']); ?></email>
		<website><?php echo wxr_cdata( $post['website'] ); ?></website>
		<ip><?php echo $post['ip']; ?></ip>
		<country><?php echo wxr_cdata($post['country']); ?></country>
		<address><?php echo wxr_cdata($post['address']); ?></address>
		<read><?php echo $post['read']; ?></read>
		<status><?php echo $post['status']; ?></status>
		<type><?php echo $post['type']; ?></type>
	</item>
<?php
	}
?>
</channel>
</rss>
<?php
}
	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 */
	function wxr_cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );

		// $str = ent2ncr(esc_html($str));
		$str = "<![CDATA[$str" . ( ( substr( $str, -1 ) == ']' ) ? ' ' : '' ) . ']]>';

		return $str;
	}
function mysql2date( $dateformatstring, $mysqlstring) {
	$m = $mysqlstring;
	if ( empty( $m ) )
		return false;

	if ( 'G' == $dateformatstring )
		return strtotime( $m . ' +0000' );

	$i = strtotime( $m );

	if ( 'U' == $dateformatstring )
		return $i;

		return date( $dateformatstring, $i );
}
function seems_utf8($str) {
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
				return false;
		}
	}
	return true;
}
function wp_parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

include 'header.php';
 		echo '<div class="wrap">';
        echo   '<h2>导出</h2>';
		echo   '<div class="clear"></div>';
		echo	'<p>数据导出仅导出所有询盘内容，不导出用户及其它设置.导出的格式为xml文件</p>';
		echo	'<form action="" method="get" id="export-filters">';
		echo	'<input type="hidden" name="download" value="true">';
		echo		'<p class="submit"><input type="submit" name="submit" id="submit" class="button-secondary" value="下载导出文件"></p>';
		echo	'</form>';
		echo '</div>';
include 'footer.php';
?>