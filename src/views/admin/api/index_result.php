<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.0.1.9
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
		let is_loading = false;
		const loading_message = '<?php $instance->h( 'loading', true );?>...';
		$( document ).on( 'click', '.wrpj_show_index_result_button', function() {
			if ( is_loading ) {
				return false;
			}
			is_loading = true;

			const post_id = $( this ).data( 'id' );
			const api_class = window[ '<?php $instance->h( $api_class );?>' ];
			const modal_class = window[ '<?php $instance->modal_class();?>' ];
			api_class.ajax( 'index_result', { p: post_id } ).done( function( json ) {
				modal_class.hide_loading();
				modal_class.show_message( json.message );
				$( '#<?php $instance->id(); ?>-modal-message-wrap' ).animate( { scrollTop: 0 } );
			} ).fail( function( err ) {
				modal_class.hide();
				console.log( err );
			} ).always( function() {
				is_loading = false;
			} );

			modal_class.show( true, function() {
				api_class.abort( 'index_result' );
				modal_class.hide();
			}, loading_message );
			return false;
		} );
	} )( jQuery );
</script>
