<?php
/**
 * @version 1.3.12
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
/** @var array $settings */
/** @var array $category_data */
/** @var string $exclude_post_ids */
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
						'name'       => 'exclude_categories[]',
						'label'      => $data['name'] . ' (' . implode( ', ', $instance->app->array->pluck( $data['post_types'], 'label' ) ) . ')',
						'value'      => $slug,
						'checked'    => $data['excluded'],
						'class'      => 'check-checked-changed',
						'attributes' => [
							'data-value' => $data['excluded'],
						],
					] ); ?>
                </div>
			<?php endforeach; ?>
        </td>
    </tr>
    <tr>
        <th>
			<?php $instance->h( 'Exclude post ids', true ); ?>
        </th>
        <td>
			<?php $instance->form( 'input/text', $args, [
				'name'       => 'exclude_ids',
				'value'      => $exclude_post_ids,
				'class'      => 'check-value-changed',
				'attributes' => [
					'data-value' => $exclude_post_ids,
				],
			] ); ?>
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
						'class' => 'on-exclude-word word-exclude-button button-primary',
					] ); ?>
					<?php $instance->form( 'input/button', $args, [
						'value' => '',
						'name'  => '',
						'class' => 'off-exclude-word word-exclude-button button-primary',
					] ); ?>
                </span>
            </div>
            <div id="exclude-buttons">
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'prev',
					'value' => 'Prev',
					'class' => 'button-primary',
					'id'    => 'excluded-words-prev',
				] ); ?>
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'next',
					'value' => 'Next',
					'class' => 'button-primary',
					'id'    => 'excluded-words-next',
				] ); ?>
				<?php $instance->form( 'input/button', $args, [
					'name'  => 'reload',
					'value' => 'Reload',
					'class' => 'button-primary word-exclude-button',
					'id'    => 'excluded-words-reload',
				] ); ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            <label for="<?php $instance->h( $settings['ranking_threshold']['id'] ); ?>"><?php $instance->h( 'Related posts threshold (0～1)', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/number', $args, $settings['ranking_threshold'] ); ?>
        </td>
    </tr>
    <tr>
        <th>
            <label for="<?php $instance->h( $settings['search_threshold']['id'] ); ?>"><?php $instance->h( 'Search threshold (0～1)', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/number', $args, $settings['search_threshold'] ); ?>
        </td>
    </tr>
</table>
