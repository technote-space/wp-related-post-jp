<?php
/**
 * @version 1.1.1
 * @author technote-space
 * @since 1.0.2.1
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
/** @var array $args */
/** @var string $admin_page_url */
/** @var array $related_posts_title */
/** @var array $ranking_number */
/** @var array $auto_insert_related_post */
?>

<?php $instance->form( 'open', $args ); ?>
<div id="<?php $instance->id(); ?>-dashboard" class="wrap narrow">
    <h2 class="nav-tab-wrapper wp-clearfix" data-admin_page_url="<?php $instance->h( $admin_page_url ); ?>">
        <a href="#" data-target="main" class="nav-tab"><?php $instance->h( 'Basic Settings', true ); ?></a>
        <a href="#" data-target="exclude" class="nav-tab"><?php $instance->h( 'Exclude Settings', true ); ?></a>
        <a href="#" data-target="insert" class="nav-tab"><?php $instance->h( 'Auto Insert Settings', true ); ?></a>
        <a href="#" data-target_page="related_post-setting" class="nav-tab"><?php $instance->h( 'Go to Detail Settings', true ); ?></a>
    </h2>
    <div id="<?php $instance->id(); ?>-tab-content-wrap">
        <div class="<?php $instance->id(); ?>-tab-content" data-tab="main">
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
						<?php $instance->form( 'input/text', $args, $ranking_number ); ?>
                    </td>
                </tr>
            </table>
            <!--        <h3>デザイン</h3>-->
        </div>
        <div class="<?php $instance->id(); ?>-tab-content active" data-tab="exclude">
            <h3>除外カテゴリ</h3>
            <!--        <h3>古い記事の除外</h3>-->
        </div>
        <div class="<?php $instance->id(); ?>-tab-content active" data-tab="insert">
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
        </div>

		<?php $instance->form( 'input/submit', $args, [
			'name'  => 'update',
			'value' => 'Update',
			'class' => 'button-primary left',
		] ); ?>
    </div>

</div>
<?php $instance->form( 'close', $args ); ?>





