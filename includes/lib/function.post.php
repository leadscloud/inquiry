<?php
/**
 * 统计文章数量
 *
 * @param string $type
 * @return int
 */
function post_count($type) {
    $db = get_conn(); return $db->result(sprintf("SELECT COUNT(`postid`) FROM `wp_inquiry` WHERE `type`='%s' AND `approved`='passed';", $type));
}
?>