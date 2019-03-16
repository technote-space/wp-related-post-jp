<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.3.2
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $related_posts_title */
/** @var array $ranking_number */
?>
<table class="form-table">
    <tr>
        <th>
            <label for="<?php $instance->h( $related_posts_title['id'] ); ?>"><?php $instance->h( 'Related posts title', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/text', $args, $related_posts_title ); ?>
        </td>
    </tr>
    <tr>
        <th>
            <label for="<?php $instance->h( $ranking_number['id'] ); ?>"><?php $instance->h( 'Display Count', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/number', $args, $ranking_number ); ?>
        </td>
    </tr>
    <tr>
        <th><?php $instance->h( 'Design', true ); ?></th>
        <td>
            実装予定...
        </td>
    </tr>
</table>
