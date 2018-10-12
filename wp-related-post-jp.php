<?php
/*
Plugin Name: WP Related Post JP
Plugin URI:
Description: Plugin Description
Author: technote
Version: 1.0.2.0
Author URI: https://technote.space
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Technote::get_instance( 'Related_Post', __FILE__ );

@require_once dirname( __FILE__ ) . DS . 'functions.php';
