<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.2.8.1 Changed: use mprogress.js instead of jquery-ui-progressbar
 * @since 1.3.0 Changed: trivial change
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var string $api_class */
$instance->js( 'mprogress.min.js' );
?>

<script>
    (function ($) {
        const mprogress = new Mprogress({
            template: 2,
            parent: '#related_post-progressbar-wrap .progressbar'
        });

        let is_valid_button = true;
        $('#<?php $instance->id(); ?>-switch-buttons .index_on').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('index_on').done(function (json) {

            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });
        $('#<?php $instance->id(); ?>-switch-buttons .index_off').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('index_off').done(function (json) {

            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });
        $('#<?php $instance->id(); ?>-finished-wrap .index_clear').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('index_clear').done(function (json) {

            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });

        const check_progress = function (repeat) {
            window.<?php $instance->h( $api_class );?>.ajax('progress').done(function (json) {
                /** @var {{posts_indexed: boolean, is_valid_posts_index: boolean, processed_rate: number}} json */
                if (json.posts_indexed) {
                    // すでに初期化済み
                    $('#<?php $instance->id(); ?>-switch-buttons').hide();
                    $('#<?php $instance->id(); ?>-progressbar-wrap').hide();
                    $('#<?php $instance->id(); ?>-info-wrap').hide();
                    $('#<?php $instance->id(); ?>-finished-wrap').show();
                    if (is_valid_button) {
                        $('#<?php $instance->id(); ?>-finished-wrap .index_clear').removeClass('disabled').show();
                    }
                } else if (json.is_valid_posts_index) {
                    // 更新が有効
                    if (is_valid_button) {
                        $('#<?php $instance->id(); ?>-switch-buttons .index_on').hide();
                        $('#<?php $instance->id(); ?>-switch-buttons .index_off').removeClass('disabled').show();
                        $('#<?php $instance->id(); ?>-switch-buttons').show();
                    }
                    $('#<?php $instance->id(); ?>-progressbar-wrap').show();
                    $('#<?php $instance->id(); ?>-info-wrap .loading').text(json.processed + ' / ' + json.total);
                    mprogress.set(json.processed_rate / 100);
                    $('#<?php $instance->id(); ?>-info-wrap').show();
                    $('#<?php $instance->id(); ?>-info-wrap .next').text(json.next);
                    $('#<?php $instance->id(); ?>-finished-wrap').hide();
                } else {
                    // 更新が無効
                    if (is_valid_button) {
                        $('#<?php $instance->id(); ?>-switch-buttons .index_on').removeClass('disabled').show();
                        $('#<?php $instance->id(); ?>-switch-buttons .index_off').hide();
                        $('#<?php $instance->id(); ?>-switch-buttons').show();
                    }
                    $('#<?php $instance->id(); ?>-progressbar-wrap').hide();
                    $('#<?php $instance->id(); ?>-info-wrap').hide();
                    $('#<?php $instance->id(); ?>-finished-wrap').hide();
                }
            }).fail(function (err) {
                console.log(err);
            }).always(function () {
                if (repeat) {
                    setTimeout(function () {
                        check_progress(repeat);
                    }, 5000);
                }
            });
        };
        check_progress(true);
    })(jQuery);
</script>
