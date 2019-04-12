<?php
/**
 * @version 1.3.9
 * @author Technote
 * @since 1.0.2.3
 * @since 1.3.0 Changed: trivial change
 * @since 1.3.2 Improved: refactoring
 * @since 1.3.2 Added: 除外ワード (#22)
 * @since 1.3.6 Changed: trivial change
 * @since 1.3.9 #67
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var string $api_class */
?>

<script>
	( function ( $ ) {
		$( '#<?php $instance->id(); ?>-dashboard .nav-tab' ).click( function () {
			const page = $( this ).data( 'target_page' );
			if ( page ) {
				location.href = $( this ).closest( 'h2' ).data( 'admin_page_url' ) + page;
				return false;
			}
			$( '#<?php $instance->id(); ?>-dashboard .nav-tab' ).removeClass( 'nav-tab-active' );
			$( '#<?php $instance->id(); ?>-dashboard .<?php $instance->id(); ?>-tab-content' ).removeClass( 'active' );
			$( this ).addClass( 'nav-tab-active' );
			$( '.<?php $instance->id(); ?>-tab-content[data-tab="' + $( this ).data( 'target' ) + '"]' ).addClass( 'active' );
			location.hash = $( this ).data( 'target' );
			const action  = $( this ).closest( 'form' ).attr( 'action' ).replace( /#\w+$/, '' ) + '#' + $( this ).data( 'target' );
			$( this ).closest( 'form' ).attr( 'action', action );
			return false;
		} );

		const hash = location.hash;
		let tab;
		if ( hash ) {
			tab = $( '[data-target="' + location.hash.replace( /^#/, '' ) + '"]' ).eq( 0 );
			if ( tab.length <= 0 ) {
				tab = null;
			}
		}
		if ( ! tab ) {
			tab = $( '#<?php $instance->id(); ?>-dashboard .nav-tab' ).eq( 0 );
		}
		tab.trigger( 'click' );

		let page                 = 1, is_loading = false, has_next = false, length;
		const api_class          = window[ '<?php $instance->h( $api_class );?>' ];
		const get_excluded_words = function ( p ) {
			if ( is_loading ) {
				return;
			}
			is_loading = true;
			$( '#exclude-word-wrap input[type="button"]' ).prop( 'disabled', true );
			api_class.ajax( 'word_get', { page: p } ).done( function ( json ) {
				has_next = json.has_next;
				$( '#excluded-words' ).html( '' );
				Object.keys( json.words ).forEach( function ( key ) {
					const word = json.words[ key ];
					$( '#exclude-word-buttons-template .on-exclude-word' ).val( word.word ).attr( 'data-word', word.word );
					$( '#exclude-word-buttons-template .off-exclude-word' ).val( word.word ).attr( 'data-word', word.word );
					const buttons = $( '#exclude-word-buttons-template' ).html();
					$( '#excluded-words' ).append( buttons );
				} );
				length = json.words.length;
			} ).fail( function ( err ) {
				console.log( err );
			} ).always( function () {
				is_loading = false;
				page       = p;
				$( '#exclude-word-wrap input[type="button"' ).prop( 'disabled', false );

				if ( ! has_next ) {
					$( '#excluded-words-next' ).prop( 'disabled', true );
				}
				if ( page <= 1 ) {
					$( '#excluded-words-prev' ).prop( 'disabled', true );
				} else if ( length <= 0 ) {
					get_excluded_words( page - 1 );
				}
			} );
		};
		$( '#excluded-words-prev' ).on( 'click', function () {
			if ( page <= 1 ) {
				return false;
			}
			get_excluded_words( page - 1 );
			return false;
		} );
		$( '#excluded-words-next' ).on( 'click', function () {
			if ( ! has_next ) {
				return false;
			}
			get_excluded_words( page + 1 );
			return false;
		} );
		$( '#excluded-words-reload' ).on( 'click', function () {
			get_excluded_words( page );
			return false;
		} );
		get_excluded_words( page );

		const check_changed = function () {
			let result = false;
			$( '.check-value-changed' ).each( function () {
				result |= $( this ).val() + '' !== $( this ).data( 'value' ) + '';
			} );
			$( '.check-checked-changed' ).each( function () {
				result |= $( this ).prop( 'checked' ) !== ( $( this ).data( 'value' ) !== '' );
			} );
			return result;
		};
		$( window ).on( 'submit', function () {
			$( window ).off( 'beforeunload' );
		} );
		$( window ).on( 'beforeunload', function () {
			if ( check_changed() ) {
				return '<?php $instance->h( 'Are you sure you want to discard the changes?', true );?>';
			}
		} );
	} )( jQuery );
</script>
