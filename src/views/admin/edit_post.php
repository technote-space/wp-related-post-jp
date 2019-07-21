<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Traits\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var int $post_id */
$instance->form( 'input/button', $args, [
	'class'      => 'wrpj_show_index_result_button button-primary',
	'name'       => 'show',
	'value'      => 'Show',
	'attributes' => [
		'data-id' => $post_id,
	],
] );
