<?php
/**
 * @version 1.3.2
 * @author technote-space
 * @since 1.0.2.1
 * @since 1.3.0 Changed: trivial change
 * @since 1.3.2 Improved: refactoring
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $tabs */
/** @var string $admin_page_url */
/** @var array $related_posts_title */
/** @var array $ranking_number */
/** @var array $auto_insert_related_post */
?>

<?php $instance->form( 'open', $args ); ?>
<div id="<?php $instance->id(); ?>-dashboard" class="wrap narrow">
    <h2 class="nav-tab-wrapper wp-clearfix" data-admin_page_url="<?php $instance->h( $admin_page_url ); ?>">
		<?php foreach ( $tabs as $tab => $name ): ?>
            <a href="#" data-target="<?php $instance->h( $tab ); ?>" class="nav-tab"><?php $instance->h( $name, true ); ?></a>
		<?php endforeach; ?>
        <a href="#" data-target_page="related_post-setting" class="nav-tab"><?php $instance->h( 'Go to Detail Settings', true ); ?></a>
    </h2>
    <div id="<?php $instance->id(); ?>-tab-content-wrap">
		<?php foreach ( $tabs as $tab => $name ): ?>
            <div class="<?php $instance->id(); ?>-tab-content" data-tab="<?php $instance->h( $tab ); ?>">
				<?php /** @noinspection PhpIncludeInspection */
				include 'dashboard/' . $tab . '.php'; ?>
            </div>
		<?php endforeach; ?>
		<?php $instance->form( 'input/submit', $args, [
			'name'  => 'update',
			'value' => 'Update',
			'class' => 'button-primary large',
		] ); ?>
    </div>

</div>
<?php $instance->form( 'close', $args ); ?>





