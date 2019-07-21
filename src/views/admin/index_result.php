<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Traits\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var WP_Post $post */
/** @var WP_Post[]|false $posts */
/** @var array $words */
/** @var bool $indexed */
/** @var bool $setup_ranking */
include 'style/index_result.php';
?>
<h2><?php $instance->h( $post->post_title ); ?></h2>
<div class="ranking-posts">
    <h3><?php $instance->h( 'Related Posts', true ); ?></h3>
	<?php if ( ! $setup_ranking ): ?>
        <div class="error-message">
			<?php $instance->h( 'Not finished ranking process.', true ); ?>
        </div>
	<?php else: ?>
        <table class="widefat striped">
            <tr>
                <th><?php $instance->h( 'Rank', true ); ?></th>
                <th><?php $instance->h( 'Post ID', true ); ?></th>
                <th><?php $instance->h( 'Post Title', true ); ?></th>
                <th><?php $instance->h( 'Score', true ); ?></th>
            </tr>
			<?php if ( empty( $posts ) || count( $posts ) <= 0 ): ?>
                <tr>
                    <td colspan="4"><?php $instance->h( 'Item not found.', true ); ?></td>
                </tr>
			<?php else: ?>
				<?php $n = 1; ?>
				<?php foreach ( $posts as $p ): ?>
                    <tr>
                        <td><?php $instance->h( $n++ ); ?></td>
                        <td><?php $instance->h( $p->ID ); ?></td>
                        <td><?php $instance->url( get_permalink( $p->ID ), $p->post_title, false, true ); ?></td>
                        <td><?php /** @noinspection PhpUndefinedFieldInspection */
							$instance->h( round( $p->score, 4 ) ); ?></td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
        </table>
	<?php endif; ?>
</div>
<div class="important-words">
    <h3><?php $instance->h( 'Important Words', true ); ?></h3>
	<?php if ( ! $indexed ): ?>
        <div class="error-message">
			<?php $instance->h( 'Not finished index process.', true ); ?>
        </div>
	<?php else: ?>
        <table class="widefat striped">
            <tr>
                <th><?php $instance->h( 'Word', true ); ?></th>
                <th><?php $instance->h( 'tf', true ); ?></th>
                <th><?php $instance->h( 'idf', true ); ?></th>
                <th><?php $instance->h( 'tf-idf', true ); ?></th>
                <th><?php $instance->h( 'Exclude', true ); ?> </th>
            </tr>
			<?php if ( count( $words ) <= 0 ): ?>
                <tr>
                    <td colspan="5"><?php $instance->h( 'Item not found.', true ); ?></td>
                </tr>
			<?php else: ?>
				<?php foreach ( $words as $w ): ?>
                    <tr>
                        <td><?php $instance->h( $w['word'] ); ?></td>
                        <td><?php $instance->h( $w['tf'] ); ?></td>
                        <td><?php $instance->h( $w['idf'] ); ?></td>
                        <td><?php $instance->h( round( $w['tfidf'], 4 ) ); ?></td>
                        <td class="exclude-word-buttons-wrap">
							<?php $instance->form( 'input/button', $args, [
								'value'      => 'Exclude',
								'class'      => 'on-exclude-word word-exclude-button button-primary',
								'attributes' => [
									'data-word' => $w['word'],
								],
							] ); ?>
							<?php $instance->form( 'input/button', $args, [
								'value'      => 'Cancel',
								'class'      => 'off-exclude-word word-exclude-button button-primary',
								'attributes' => [
									'data-word' => $w['word'],
								],
							] ); ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
			<?php endif; ?>
        </table>
	<?php endif; ?>
</div>

<?php $instance->form( 'input/button', $args, [
	'class'      => 'button-primary',
	'name'       => 'close',
	'value'      => 'Close',
	'attributes' => [
		'onclick' => 'window.' . $instance->modal_class( false ) . '.hide(); return false;',
		'style'   => 'float: right; margin-top: 10px;',
	],
] ); ?>
