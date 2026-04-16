<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (!defined('FBW_REMOVE_CONTENT_ON_UNINSTALL') || FBW_REMOVE_CONTENT_ON_UNINSTALL !== true) {
	exit;
}

$posts = get_posts([
	'post_type'      => 'fx-builder-content',
	'posts_per_page' => -1,
	'post_status'    => 'any',
]);

foreach ($posts as $post) {
	wp_delete_post($post->ID, true);
}
