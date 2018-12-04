<?php
/**
 * @version 1.1.1
 * @author technote-space
 * @since 1.0.1.9
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
?>

<style>
    #<?php $instance->id(); ?>-modal-message .widefat th {
        text-align: center;
    }

    #<?php $instance->id(); ?>-modal-message .important-words {
        margin-top: 5px;
    }

    #<?php $instance->id(); ?>-modal-message h3 {
        margin-bottom: 5px;
        background: #e8e7e7;
        border: 1px solid #999;
        display: inline-block;
        padding: 6px;
    }

    #<?php $instance->id(); ?>-modal-message .error-message {
        margin: 8px;
        font-size: 1.1em;
    }
</style>