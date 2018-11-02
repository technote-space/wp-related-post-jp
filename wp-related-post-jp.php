<?php
/*
Plugin Name: WP Related Post JP
Plugin URI:
Description: WP Related Post JP provides functions to get related posts.
Author: technote
Version: 1.0.3.0
Author URI: https://technote.space
Text Domain: wrpj
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Technote::get_instance( 'Related_Post', __FILE__ );
