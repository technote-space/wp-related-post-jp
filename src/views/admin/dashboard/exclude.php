<?php
/**
 * @version 1.3.4
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
			<?php $instance->h( 'Exclude categories', true ); ?>
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
    <tr>
        <th>
			<?php $instance->h( 'Excluded words', true ); ?>
        </th>
        <td id="exclude-word-wrap">
            <div id="excluded-words"></div>
            <div id="exclude-word-buttons-template">
                <span class="exclude-word-buttons-wrap">
					<?php $instance->form( 'input/button', $args, [
						'value' => '',
						'name'  => '',
						'class' => 'on-exclude-word word-exclude-button button-primary left',
					] ); ?>
					<?php $instance->form( 'input/button', $args, [
						'value' => '',
						'name'  => '',
						'class' => 'off-exclude-word word-exclude-button button-primary left',
					] ); ?>
                </span>
            </div>
            <div id="exclude-buttons">
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'prev',
					'value' => 'Prev',
					'class' => 'button-primary left',
					'id'    => 'excluded-words-prev',
				] ); ?>
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'next',
					'value' => 'Next',
					'class' => 'button-primary left',
					'id'    => 'excluded-words-next',
				] ); ?>
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'reload',
					'value' => 'Reload',
					'class' => 'button-primary left',
					'id'    => 'excluded-words-reload',
				] ); ?>
            </div>
        </td>
    </tr>
</table>
