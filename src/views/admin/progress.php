<?php
/**
 * @version 1.3.6
 * @author Technote
 * @since 1.0.0.0
 * @since 1.3.0 Changed: trivial change
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
?>

<div id="<?php $instance->id(); ?>-switch-buttons">
	<?php $instance->form( 'input/button', $args, [
		'class' => 'index_on button-primary',
		'name'  => 'index_on',
		'value' => 'On index posts',
	] ); ?>
	<?php $instance->form( 'input/button', $args, [
		'class' => 'index_off button-primary',
		'name'  => 'index_off',
		'value' => 'Off index posts',
	] ); ?>
</div>
<div id="<?php $instance->id(); ?>-progressbar-wrap">
    <div class="progressbar"></div>
</div>
<div id="<?php $instance->id(); ?>-info-wrap">
    <div class="loading"></div>
    <div class="next"></div>
</div>
<div id="<?php $instance->id(); ?>-finished-wrap">
    <div class="message"><?php $instance->h( 'Posts index has successfully completed.', true ); ?></div>
	<?php $instance->form( 'input/button', $args, [
		'class' => 'index_clear button-primary',
		'name'  => 'index_clear',
		'value' => 'Clear index data',
	] ); ?>
</div>
