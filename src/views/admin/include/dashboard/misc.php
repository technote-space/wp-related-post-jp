<?php
/**
 * @version 1.3.14
 * @author Technote
 * @since 1.3.14
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $settings */
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
    <tr>
        <th>
            <label for="<?php $instance->h( $settings['use_admin_ajax']['id'] ); ?>"><?php $instance->h( $settings['use_admin_ajax']['title'] ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/checkbox', $args, $settings['use_admin_ajax'] ); ?>
        </td>
    </tr>
</table>