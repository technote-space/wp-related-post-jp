<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [

	'\Related_Post\Classes\Models\Control' => [
		'transition_post_status' => [
			'transition_post_status' => [],
		],

		'${prefix}app_initialized' => [
			'setup_index_posts' => [],
		],
		'${prefix}changed_option'  => [
			'changed_option' => [],
		],

		'pre_get_posts'            => [
			'pre_get_posts' => [],
		],
		'${prefix}on_related_post' => [
			'on_related_post' => [],
		],

		'${prefix}post_load_admin_page' => [
			'post_load_admin_page' => [],
		],

		'admin_head-edit.php' => [
			'edit_post_page' => [],
		],
		'wp_ajax_fetch-list'  => [
			'edit_post_page' => [ 0 ],
		],
		'wp_ajax_inline-save' => [
			'edit_post_page' => [ 0 ],
		],

		'the_content' => [
			'the_content' => [],
		],

		'${prefix}app_activated' => [
			'init_posts_rankings' => [],
		],
	],
];