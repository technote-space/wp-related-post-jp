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
/** @var \Technote\Controllers\Admin\Base $instance */
$instance->css('jquery-ui.min.css');
?>

<style>
    #<?php $instance->id(); ?>-switch-buttons,
    #<?php $instance->id(); ?>-progressbar-wrap,
    #<?php $instance->id(); ?>-finished-wrap,
    #<?php $instance->id(); ?>-info-wrap {
        display: none;
    }

    #<?php $instance->id(); ?>-info-wrap .loading {
        margin: 5px;
        text-align: center;
        font-size: 1.4em;
    }

    #<?php $instance->id(); ?>-info-wrap .next {
        margin: 5px;
        text-align: center;
        font-size: 1.4em;
    }

    #<?php $instance->id(); ?>-finished-wrap .message {
        margin: 5px;
        font-size: 1.4em;
    }
</style>