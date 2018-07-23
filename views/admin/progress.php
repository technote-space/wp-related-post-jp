<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
/** @var array $args */
?>

<div id="<?php $instance->id(); ?>-switch-buttons">
	<?php $instance->form( 'input/button', $args, array(
		'class' => 'index_on button-primary left',
		'name'  => 'index_on',
		'value' => 'On index posts',
	) ); ?>
	<?php $instance->form( 'input/button', $args, array(
		'class' => 'index_off button-primary left',
		'name'  => 'index_off',
		'value' => 'Off index posts',
	) ); ?>
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
	<?php $instance->form( 'input/button', $args, array(
		'class' => 'ranking_clear button-primary left',
		'name'  => 'ranking_clear',
		'value' => 'Clear ranking data',
	) ); ?>
</div>
