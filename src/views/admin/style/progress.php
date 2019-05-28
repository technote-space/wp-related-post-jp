<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
$instance->css( 'mprogress.min.css' );
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

    #<?php $instance->id(); ?>-progressbar-wrap .progressbar {
        margin: 10px 0;
        width: 100%;
        height: 10px;
    }
</style>