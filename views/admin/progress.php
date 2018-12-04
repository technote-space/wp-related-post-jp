<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
/** @var array $args */
?>

<div id="<?php $instance->id(); ?>-switch-buttons">
	<?php $instance->form( 'input/button', $args, [
		'class' => 'index_on button-primary left',
		'name'  => 'index_on',
		'value' => 'On index posts',
	] ); ?>
	<?php $instance->form( 'input/button', $args, [
		'class' => 'index_off button-primary left',
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
		'class' => 'index_clear button-primary left',
		'name'  => 'index_clear',
		'value' => 'Clear index data',
	] ); ?>
</div>
