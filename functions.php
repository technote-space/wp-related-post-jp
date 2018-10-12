<?php
/**
 * @version 1.0.1.9
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

add_action( 'get_template_part_tmp/related-list', function () {
	do_action( 'related_post-on_related_post' );
} );

// cocoon用
add_action( 'after_setup_theme', function () {
	remove_action( 'after_setup_theme', 'code_minify_buffer_start', 99999999 );
} );
