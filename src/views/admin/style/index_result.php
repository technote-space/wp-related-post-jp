<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
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

	#<?php $instance->id(); ?>-modal-message .off-exclude-word {
		display: none;
		background: #ea6666;
		border-color: #ff2929;
		box-shadow: 0 1px 0 #ff2929;
		text-shadow: 0 -1px 1px #ff2929, 1px 0 1px #ff2929, 0 1px 1px #ff2929, -1px 0 1px #ff2929;
	}

	#<?php $instance->id(); ?>-modal-message .off-exclude-word:hover {
		background: #ff7777;
	}
</style>
