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
?>
<table class="form-table">
	<tr>
		<th>
			<label for="<?php $instance->h( $settings['related_posts_title']['id'] ); ?>"><?php $instance->h( 'Related posts title', true ); ?></label>
		</th>
		<td>
			<?php $instance->form( 'input/text', $args, $settings['related_posts_title'] ); ?>
		</td>
	</tr>
	<tr>
		<th>
			<label for="<?php $instance->h( $settings['ranking_number']['id'] ); ?>"><?php $instance->h( 'Display Count', true ); ?></label>
		</th>
		<td>
			<?php $instance->form( 'input/number', $args, $settings['ranking_number'] ); ?>
		</td>
	</tr>
	<tr>
		<th><?php $instance->h( 'Design', true ); ?></th>
		<td>
			実装予定...
		</td>
	</tr>
</table>
