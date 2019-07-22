<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var array $args */
/** @var array $settings */
/** @var bool $is_valid_rest_api */
?>
<table class="form-table">
	<tr>
		<th>
			<label for="<?php $instance->h( $settings['auto_insert_related_post']['id'] ); ?>"><?php $instance->h( 'Auto insert related posts', true ); ?></label>
		</th>
		<td>
			<?php $instance->form( 'input/checkbox', $args, $settings['auto_insert_related_post'] ); ?>
		</td>
	</tr>
	<?php if ( $is_valid_rest_api ) : ?>
		<tr>
			<th>
				<label for="<?php $instance->h( $settings['use_admin_ajax']['id'] ); ?>"><?php $instance->h( $settings['use_admin_ajax']['title'] ); ?></label>
			</th>
			<td>
				<?php $instance->form( 'input/checkbox', $args, $settings['use_admin_ajax'] ); ?>
			</td>
		</tr>
	<?php endif; ?>
</table>
