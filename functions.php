<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}

// cocoon用
if ( strpos( wp_get_theme()->get_template(), 'cocoon' ) !== false ) {
	add_action( 'get_template_part_tmp/related-list', function () {
		do_action( 'related_post/on_related_post' );
	} );

	add_action( 'after_setup_theme', function () {
		remove_action( 'after_setup_theme', 'code_minify_buffer_start', 99999999 );
	} );

	add_action( 'related_post/app_initialize', function ( $app ) {
		/** @var \WP_Framework $app */
		$app->setting->edit_setting( 'auto_insert_related_post', 'default', false );
		$app->setting->remove_setting( 'assets_version' );
	} );
}

// allowed wp tables
add_filter( 'related_post/allowed_wp_tables', function ( $tables ) {
	/** @var \wpdb $wpdb */
	global $wpdb;
	$tables[ $wpdb->term_relationships ] = $wpdb->term_relationships;

	return $tables;
} );