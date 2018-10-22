<?php
/**
 * @version 1.0.2.5
 * @author technote-space
 * @since 1.0.1.9
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
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
            window.<?php $instance->h( $api_class );?>.ajax('wrpj_index_result', {p: post_id}).then(function (json) {
                window.<?php $instance->modal_class();?>.hide_loading();
                window.<?php $instance->modal_class();?>.show_message(json.message);
                $('#<?php $instance->id(); ?>-modal-message-wrap').animate({scrollTop: 0});
            }, function (status) {
                window.<?php $instance->modal_class();?>.hide();
                console.log(status);
            }).then(function () {
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
