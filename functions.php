<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use Related_Post\Classes\Models\Control;

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

// JIN用
if ( strpos( wp_get_theme()->get_template(), 'jin' ) !== false ) {
	add_action( 'get_template_part_include/related-entries', function () {
		do_action( 'related_post/on_related_post' );
	} );
}

// SWELL用
if ( strpos( wp_get_theme()->get_template(), 'swell' ) !== false ) {
	add_filter( 'loos_related_post_args', function ( $args ) {
		do_action( 'related_post/on_related_post' );

		return $args;
	} );
}

if ( ! function_exists( 'wp_related_posts' ) ) {
	function wp_related_posts() {
		/** @var Control $control */
		$control = Control::get_instance( WP_Framework::get_instance( WP_RELATED_POST_JP ) );
		echo $control->get_related_posts_content();
	}
}

if ( ! function_exists( 'get_related_posts' ) ) {
	/**
	 * @param int|WP_Post|null $_post
	 *
	 * @return WP_Post[]|false
	 */
	function get_related_posts( $_post = null ) {
		/** @var Control $control */
		$control = Control::get_instance( WP_Framework::get_instance( WP_RELATED_POST_JP ) );

		return $control->get_related_posts( $_post );
	}
}

// sango用
if ( strpos( wp_get_theme()->get_template(), 'sango' ) !== false ) {
	function sng_get_related_posts_array() {
		$posts = get_related_posts();
		if ( false === $posts ) {
			return [];
		}

		return $posts;
	}
}
