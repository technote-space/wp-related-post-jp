<?php
/**
 * @version 1.3.2
 * @author technote-space
 * @since 1.3.2
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $auto_insert_related_post */
?>
<table class="form-table">
    <tr>
        <th>
            <label for="<?php $instance->h( $auto_insert_related_post['id'] ); ?>"><?php $instance->h( 'Auto insert related posts', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/checkbox', $args, $auto_insert_related_post ); ?>
        </td>
    </tr>
</table>
<!--        <h3>ランダム表示設定</h3>-->