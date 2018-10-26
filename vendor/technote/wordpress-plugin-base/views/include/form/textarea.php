<?php
/**
 * Technote Views Include Form Textarea
 *
 * @version 1.1.25
 * @author technote-space
 * @since 1.1.25
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
/** @var array $args */
/** @var string $id */
/** @var string $class */
/** @var string $name */
/** @var string $value */
/** @var array $attributes */
empty( $attributes ) and $attributes = [];
isset( $id ) and $attributes['id'] = $id;
isset( $class ) and $attributes['class'] = $class;
$attributes['name'] = $name;
! isset( $value ) and $value = '';
?>
<textarea <?php $instance->get_view( 'include/attributes', array_merge( $args, [ 'attributes' => $attributes ] ), true ); ?> ><?php echo sanitize_textarea_field( $value ); ?></textarea>