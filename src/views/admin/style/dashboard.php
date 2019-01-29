<?php
/**
 * @version 1.2.8.1
 * @author technote-space
 * @since 1.0.2.3
 * @since 1.2.8.1 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
?>

<style>
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
</style>