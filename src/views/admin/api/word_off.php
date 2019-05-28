<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.3.2
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Traits\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var string $api_class */
?>
<script>
	( function( $ ) {
		let is_sending = false;
		$( '#<?php $instance->id(); ?>-modal-message, #<?php $instance->id(); ?>-tab-content-wrap' ).on( 'click', '.on-exclude-word', function() {
			if ( is_sending ) {
				return false;
			}
			is_sending = true;

			const $this = $( this );
			$( '.word-exclude-button' ).prop( 'disabled', true );

			const word = $this.data( 'word' );
			const api_class = window[ '<?php $instance->h( $api_class );?>' ];
			api_class.ajax( 'word_off', { word: word } ).done( function() {
				$this.hide();
				$this.closest( '.exclude-word-buttons-wrap' ).find( '.off-exclude-word' ).show();
			} ).fail( function( err ) {
				console.log( err );
			} ).always( function() {
				is_sending = false;
				$( '.word-exclude-button' ).prop( 'disabled', false );
			} );
			return false;
		} );
	} )( jQuery );
</script>
