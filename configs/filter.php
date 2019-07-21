<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [

	'\Related_Post\Classes\Models\Control'      => [
		'${prefix}allowed_wp_tables' => [
			'allowed_wp_tables',
		],
		'the_content'                => [
			'the_content',
		],
	],
	'\Related_Post\Classes\Models\Post'         => [
		'transition_post_status'   => [
			'transition_post_status',
		],
		'pre_get_posts'            => [
			'pre_get_posts',
		],
		'${prefix}on_related_post' => [
			'on_related_post',
		],
		'admin_head-edit.php'      => [
			'edit_post_page',
		],
		'wp_ajax_fetch-list'       => [
			'edit_post_page' => 0,
		],
		'wp_ajax_inline-save'      => [
			'edit_post_page' => 0,
		],
	],
	'\Related_Post\Classes\Models\Update'       => [
		'${prefix}app_initialized'      => [
			'setup_index_posts',
		],
		'${prefix}changed_option'       => [
			'changed_option',
		],
		'${prefix}post_load_admin_page' => [
			'post_load_admin_page',
		],
		'${prefix}app_activated'        => [
			'init_posts_rankings',
		],
	],
	'\Related_Post\Classes\Models\Analyzer\Igo' => [
		'igo_memory_limit' => [
			'igo_memory_limit',
		],
	],
];
