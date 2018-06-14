<?php
define('ABS_PATHS',dirname(__FILE__));
include dirname(__FILE__).'/defines.php';

$db_name	=	DB_NAME;
$db_prefix	=	DB_PREFIX;
define('DB_FILE',BLOG_ROOT.'/content/'.DB_NAME);


require_once ABS_PATH.'/includes/lib/function.base.php';
//DROP TABLE IF EXISTS {$db_prefix}_inquiry_meta;
$sql = "
CREATE TABLE IF NOT EXISTS `{$db_prefix}_inquiry_meta` (
  `metaid` INTEGER PRIMARY KEY,
  `inquiryid` bigint(20)  NOT NULL,
  `key` char(50) NOT NULL,
  `value` longtext
);
DROP TABLE IF EXISTS {$db_prefix}_option;
CREATE TABLE IF NOT EXISTS `{$db_prefix}_option` (
  `option_id` INTEGER PRIMARY KEY,
  `option_name` varchar(64) NOT NULL,
  `option_value` longtext
);
";		
$db = get_conn();
$db->exec($sql);

echo 'upgrade complete!';

?>