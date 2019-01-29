<?php
/**
 * @version 1.0.1.9
 * @author technote-space
 * @since 1.0.1.9
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Traits\Presenter $instance */
/** @var int $post_id */
?>

<?php $instance->form( 'input/button', $args, [
	'class'      => 'wrpj_show_index_result_button button-primary',
	'name'       => 'show',
	'value'      => 'Show',
	'attributes' => [
		'data-id' => $post_id,
	],
] ); ?>

