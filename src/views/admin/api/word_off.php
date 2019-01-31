<?php
/**
 * @version 1.3.2
 * @author technote-space
 * @since 1.3.2
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
        let is_sending = false;
        $('#<?php $instance->id(); ?>-modal-message, #<?php $instance->id(); ?>-tab-content-wrap').on('click', '.on-exclude-word', function () {
            if (is_sending) {
                return false;
            }
            is_sending = true;

            const $this = $(this);
            $('.word-exclude-button').prop('disabled', true);

            const word = $this.data('word');
            window.<?php $instance->h( $api_class );?>.ajax('word_off', {word: word}).done(function (json) {
                $this.hide();
                $this.closest('.exclude-word-buttons-wrap').find('.off-exclude-word').show();
            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                is_sending = false;
                $('.word-exclude-button').prop('disabled', false)
            });
            return false;
        });
    })(jQuery);
</script>
