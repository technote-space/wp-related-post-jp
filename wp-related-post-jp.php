<?php
/**
 * Plugin Name: WP Related Post JP
 * Plugin URI: https://github.com/technote-space/wp-related-post-jp
 * Description: WP Related Post JP provides functions to get related posts.
 * Author: Technote
 * Version: 1.4.17
 * Author URI: https://technote.space
 * Text Domain: wrpj
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}

define( 'WP_RELATED_POST_JP', 'Related_Post' );

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

WP_Framework::get_instance( WP_RELATED_POST_JP, __FILE__ );
