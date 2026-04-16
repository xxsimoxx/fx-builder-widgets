<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (!defined('FBW_REMOVE_CONTENT_ON_UNINSTALL') || FBW_REMOVE_CONTENT_ON_UNINSTALL !== true) {
	exit;
}

$post_list = get_posts([
	'post_type'      => 'fx-builder-content',
	'posts_per_page' => -1,
	'post_status'    => 'any',
]);

foreach ($post_list as $p) {
	wp_delete_post($p->ID, true);
}
