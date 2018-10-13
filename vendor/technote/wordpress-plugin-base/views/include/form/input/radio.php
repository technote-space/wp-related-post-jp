<?php
/**
 * Technote Views Include Form Input Radio
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
/** @var string $id */
/** @var string $label */
/** @var array $args */
?>
<?php $instance->form( 'input', array_merge( $args, [
	'type' => 'radio',
] ) ); ?>
<?php if ( isset( $id, $label ) ): ?>
    <label for="<?php $instance->h( $id ); ?>"><?php $instance->h( $label, true ); ?></label>
<?php endif; ?>
