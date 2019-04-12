<?php
/**
 * @version 1.3.12
 * @author Technote
 * @since 1.0.2.3
 * @since 1.2.8.1 trivial change
 * @since 1.3.0 trivial change
 * @since 1.3.2 #22
 * @since 1.3.6 #52
 * @since 1.3.12 trivial change
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
?>

<style>
    #<?php $instance->id(); ?>-dashboard {
        display: table;
        margin: 15px 10px;
        width: 100%;
    }

    #<?php $instance->id(); ?>-content-wrap .nav-tab-wrapper {
        margin-right: 5%;
    }

    #<?php $instance->id(); ?>-tab-content-wrap {
        margin: 10px;
    }

    .<?php $instance->id(); ?>-tab-content {
        display: none;
        font-size: 1em;
        margin: 25px 25px 25px 10px;
    }

    .<?php $instance->id(); ?>-tab-content.active {
        display: block;
    }

    .<?php $instance->id(); ?>-tab-content.active .on-exclude-word {
        display: none;
        background: #ea6666;
        border-color: #ff2929;
        box-shadow: 0 1px 0 #ff2929;
        text-shadow: 0 -1px 1px #ff2929, 1px 0 1px #ff2929, 0 1px 1px #ff2929, -1px 0 1px #ff2929;
    }

    .<?php $instance->id(); ?>-tab-content.active .on-exclude-word:hover {
        background: #ff7777;
    }

    .<?php $instance->id(); ?>-tab-content.active #exclude-word-buttons-template {
        display: none;
    }

    .<?php $instance->id(); ?>-tab-content.active #exclude-buttons {
        margin-top: 10px;
    }
</style>