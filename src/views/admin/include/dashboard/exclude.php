<?php
/**
 * @version 1.3.6
 * @author Technote
 * @since 1.3.2
 * @since 1.3.6 Changed: デザイン調整 (#52)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $category_data */
/** @var array $ranking_threshold */
/** @var array $search_threshold */
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
            <label for="<?php $instance->h( $ranking_threshold['id'] ); ?>"><?php $instance->h( 'Ranking threshold', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/number', $args, $ranking_threshold ); ?>
        </td>
    </tr>
    <tr>
        <th>
            <label for="<?php $instance->h( $search_threshold['id'] ); ?>"><?php $instance->h( 'Search threshold', true ); ?></label>
        </th>
        <td>
			<?php $instance->form( 'input/number', $args, $search_threshold ); ?>
        </td>
    </tr>
</table>