<?php
/**
 * @version 1.0.2.3
 * @author technote-space
 * @since 1.0.1.9
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Traits\Presenter $instance */
/** @var string $title */
/** @var WP_Post $post */
/** @var WP_Post[] $related_posts */
?>

<div class="related_posts">
    <h3 class="related_posts_title">
		<?php $instance->h( $title ); ?>
    </h3>
    <div class="related_posts_wrap">
		<?php foreach ( $related_posts as $related_post ): ?>
			<?php /** @var WP_Post $related_post */ ?>
            <div class="related_posts_content">
                <div class="related_posts_title">
					<?php $instance->url( get_permalink( $related_post->ID ), $related_post->post_title ); ?>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
</div>
