<?php
/**
 * @version 1.3.13
 * @author Technote
 * @since 1.0.0.0
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
}

// simplicity2用
if ( strpos( wp_get_theme()->get_template(), 'simplicity2' ) !== false ) {
	add_action( 'get_template_part_related-entries', function () {
		do_action( 'related_post/on_related_post' );
	} );
}

if ( ! function_exists( 'wp_related_posts' ) ) {
	function wp_related_posts() {
		/** @var \Related_Post\Classes\Models\Control $control */
		$control = \Related_Post\Classes\Models\Control::get_instance( WP_Framework::get_instance( WP_RELATED_POST_JP ) );
		echo $control->get_related_posts_content();
	}
}

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
