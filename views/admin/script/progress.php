<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
/** @var string $api_class */
?>

<script>
    (function ($) {
        const progressbar = $('#<?php $instance->id(); ?>-progressbar-wrap');
        progressbar.progressbar({
            value: 0
        });

        let is_valid_button = true;
        $('#<?php $instance->id(); ?>-switch-buttons .index_on').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('index_on').then(function (json) {

            }, function (status) {
                console.log(status);
            }).catch(function (err) {
                console.log(err);
            }).then(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });
        $('#<?php $instance->id(); ?>-switch-buttons .index_off').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('index_off').then(function (json) {

            }, function (status) {
                console.log(status);
            }).catch(function (err) {
                console.log(err);
            }).then(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });
        $('#<?php $instance->id(); ?>-finished-wrap .ranking_clear').click(function () {
            $(this).addClass('disabled');
            if (!is_valid_button) return;
            is_valid_button = false;
            window.<?php $instance->h( $api_class );?>.ajax('ranking_clear').then(function (json) {

            }, function (status) {
                console.log(status);
            }).catch(function (err) {
                console.log(err);
            }).then(function () {
                is_valid_button = true;
                check_progress(false);
            });
            return false;
        });

        const check_progress = function (repeat) {
            window.<?php $instance->h( $api_class );?>.ajax('progress').then(function (json) {
                if (json.posts_indexed) {
                    // すでに初期化済み
                    $('#<?php $instance->id(); ?>-switch-buttons').hide();
                    $('#<?php $instance->id(); ?>-progressbar-wrap').hide();
                    $('#<?php $instance->id(); ?>-info-wrap').hide();
                    $('#<?php $instance->id(); ?>-finished-wrap').show();
                    if (is_valid_button) {
                        $('#<?php $instance->id(); ?>-finished-wrap .ranking_clear').removeClass('disabled').show();
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
                    progressbar.progressbar('value', json.processed_rate);
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
            }, function (status) {
                console.log(status);
            }).then(function () {
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
