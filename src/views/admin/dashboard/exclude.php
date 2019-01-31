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
/** @var array $category_data */
?>
<table class="form-table">
    <tr>
        <th>
            <label for="exclude-category"><?php $instance->h( 'Exclude categories', true ); ?></label>
        </th>
        <td>
			<?php foreach ( $category_data as $slug => $data ): ?>
                <div>
					<?php $instance->form( 'input/checkbox', $args, [
						'name'    => 'exclude_categories[]',
						'label'   => $data['name'] . ' (' . implode( ', ', $instance->app->utility->array_pluck( $data['post_types'], 'label' ) ) . ')',
						'value'   => $slug,
						'checked' => $data['excluded'],
					] ); ?>
                </div>
			<?php endforeach; ?>
        </td>
    </tr>
</table>
