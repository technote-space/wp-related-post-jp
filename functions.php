<?php
/**
 * @version 1.0.2.3
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

// cocoon用
if ( strpos( wp_get_theme()->get_template(), 'cocoon' ) !== false ) {
	add_action( 'get_template_part_tmp/related-list', function () {
		do_action( 'related_post-on_related_post' );
	} );

	add_action( 'after_setup_theme', function () {
		remove_action( 'after_setup_theme', 'code_minify_buffer_start', 99999999 );
	} );

	add_filter( 'related_post-initialize_setting', function ( $data ) {
		$data[8]['Index'][10]['auto_insert_related_post']['default'] = false;

		return $data;
	} );
}

// 設定変更
add_filter( 'related_post-setting_page_title', function () {
	return 'Detail Settings';
} );

add_filter( 'related_post-setting_page_priority', function () {
	return 100;
} );

add_filter( 'related_post-get_menu_slug', function () {
	return 'dashboard';
} );

// ヘルプ
add_filter( 'related_post-get_help_contents', function ( $contents, $slug ) {
	if ( 'setting' === $slug ) {
		return [
			'title' => 'カスタマイズ',
			'view'  => 'setting',
		];
	}

	return $contents;
}, 10, 2 );

// allowed wp tables
add_filter( 'related_post-allowed_wp_tables', function ( $tables ) {
	/** @var \wpdb $wpdb */
	global $wpdb;
	$tables[ $wpdb->term_relationships ] = $wpdb->term_relationships;

	return $tables;
} );