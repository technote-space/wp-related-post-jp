<?php
/**
 * @version 1.3.9
 * @author Technote
 * @since 1.0.0.0
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Added: wp_related_posts メソッドの追加 (#40)
 * @since 1.3.3 Added: 関連記事取得関数の追加 (#46)
 * @since 1.3.8 Added: simplicity2用の設定も追加 (#59)
 * @since 1.3.9 trivial change
 * @copyright Technote All Rights Reserved
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

//	add_action( 'related_post/app_initialize', function ( $app ) {
//		/** @var \WP_Framework $app */
//		$app->setting->edit_setting( 'auto_insert_related_post', 'default', false );
//	} );
}

/**
 * @since 1.3.8
 */
// simplicity2用
if ( strpos( wp_get_theme()->get_template(), 'simplicity2' ) !== false ) {
	add_action( 'get_template_part_related-entries', function () {
		do_action( 'related_post/on_related_post' );
	} );

//	add_action( 'related_post/app_initialize', function ( $app ) {
//		/** @var \WP_Framework $app */
//		$app->setting->edit_setting( 'auto_insert_related_post', 'default', false );
//	} );
}

add_action( 'related_post/app_initialize', function ( $app ) {
	/** @var \WP_Framework $app */
	$app->setting->remove_setting( 'assets_version' );
} );

// allowed wp tables
add_filter( 'related_post/allowed_wp_tables', function ( $tables ) {
	/** @var \wpdb $wpdb */
	global $wpdb;
	$tables[ $wpdb->term_relationships ] = $wpdb->term_relationships;

	return $tables;
} );

/**
 * @since 1.3.2
 */
if ( ! function_exists( 'wp_related_posts' ) ) {
	function wp_related_posts() {
		/** @var \Related_Post\Classes\Models\Control $control */
		$control = \Related_Post\Classes\Models\Control::get_instance( WP_Framework::get_instance( WP_RELATED_POST_JP ) );
		echo $control->get_related_posts_content();
	}
}

/**
 * @since 1.3.3
 */
if ( ! function_exists( 'get_related_posts' ) ) {
	/**
	 * @param null|\WP_Post $_post
	 *
	 * @return WP_Post[]|false
	 */
	function get_related_posts( $_post = null ) {
		/** @var \Related_Post\Classes\Models\Control $control */
		$control = \Related_Post\Classes\Models\Control::get_instance( WP_Framework::get_instance( WP_RELATED_POST_JP ) );

		return $control->get_related_posts( $_post->ID );
	}
}
