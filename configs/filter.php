<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return array(

	'\Related_Post\Models\Control' => array(
		'save_post'              => array(
			'save_post' => array(),
		),
		'transition_post_status' => array(
			'transition_post_status' => array(),
		),
		'delete_post'            => array(
			'delete_post' => array(),
		),

		'${prefix}app_initialized' => array(
			'setup_index_posts' => array(),
		),
		'${prefix}changed_option'  => array(
			'changed_option' => array(),
		),

		'pre_get_posts'                      => array(
			'pre_get_posts' => array(),
		),
		'${prefix}on_related_post' => array(
			'on_related_post' => array(),
		),

		'${prefix}pre_load_admin_page'  => array(
			'pre_load_admin_page' => array(),
		),
	),

	'\Related_Post\Controllers\Admin\Progress' => array(
		'admin_enqueue_scripts' => array(
			'admin_enqueue_scripts' => array(),
		),
	),

);