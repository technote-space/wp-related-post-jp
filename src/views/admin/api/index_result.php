<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.1.9
 * @since 1.3.0 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Traits\Presenter $instance */
/** @var string $api_class */
?>

<script>
    (function ($) {
        let is_loading = false;
        const loading_message = '<?php $instance->h( 'loading', true );?>...';
        $(document).on('click', '.wrpj_show_index_result_button', function () {
            if (is_loading) {
                return false;
            }
            is_loading = true;

            const post_id = $(this).data('id');
            window.<?php $instance->h( $api_class );?>.ajax('wrpj_index_result', {p: post_id}).done(function (json) {
                window.<?php $instance->modal_class();?>.hide_loading();
                window.<?php $instance->modal_class();?>.show_message(json.message);
                $('#<?php $instance->id(); ?>-modal-message-wrap').animate({scrollTop: 0});
            }).fail(function (err) {
                window.<?php $instance->modal_class();?>.hide();
                console.log(err);
            }).always(function () {
                is_loading = false;
            });

            window.<?php $instance->modal_class();?>.show(true, function () {
                window.<?php $instance->h( $api_class );?>.abort('wrpj_index_result');
                window.<?php $instance->modal_class();?>.hide();
            }, loading_message);
            return false;
        });
    })(jQuery);
</script>
