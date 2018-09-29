<?php
/**
 * Technote Configs Filter
 *
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

	'minify' => array(
		'admin_print_footer_scripts' => array(
			'output_js' => array( 999 ),
		),
		'admin_head'                 => array(
			'output_css' => array( 999 ),
		),
		'admin_footer'               => array(
			'output_css' => array( 999 ),
		),

		'wp_print_footer_scripts' => array(
			'output_js'  => array( 999 ),
			'output_css' => array( 998 ),
		),
		'wp_print_styles'         => array(
			'output_css' => array( 999 ),
		),
	),

	'loader->admin' => array(
		'admin_menu'    => array(
			'add_menu' => array(),
		),
		'admin_notices' => array(
			'admin_notice' => array(),
		),
	),

	'loader->api' => array(
		'rest_api_init' => array(
			'register_api' => array(),
		),
		'wp_footer'     => array(
			'register_script' => array(),
		),
		'admin_footer'  => array(
			'register_script' => array(),
		),
	),

);