<?php
/**
 * Technote Views Include Script Modal
 *
 * @version 1.1.13
 * @author technote
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
?>

<script>
    (function ($) {
        if (window.<?php $instance->modal_class();?> !== undefined) {
            return;
        }

        class <?php $instance->modal_class();?> {
            constructor() {
                const target = '<?php if ( is_admin() ): echo '#wpwrap'; else: echo '#container'; endif;?>';
                const html = '<div id="<?php $instance->id();?>-modal"><div class="<?php $instance->id();?>-loading"></div><div class="<?php $instance->id();?>-loading-message"></div></div><div id="<?php $instance->id();?>-modal-message-wrap"><div id="<?php $instance->id();?>-modal-message"></div></div>';
                $(html).prependTo(target).hide();
                $('#<?php $instance->id();?>-modal-message').click(function () {
                    return false;
                });
            }

            _modal() {
                return $('#<?php $instance->id();?>-modal');
            }

            _loading() {
                return $('#<?php $instance->id();?>-modal .<?php $instance->id();?>-loading');
            }

            _loading_message() {
                return $('#<?php $instance->id();?>-modal .<?php $instance->id();?>-loading-message');
            }

            _message_wrap() {
                return $('#<?php $instance->id();?>-modal-message-wrap');
            }

            _message() {
                return $('#<?php $instance->id();?>-modal-message');
            }

            _modal_and_message_wrap() {
                return $('#<?php $instance->id();?>-modal, #<?php $instance->id();?>-modal-message-wrap');
            }

            show(loading, click, message) {
                this._modal().fadeIn();
                if (loading) {
                    this._loading().fadeIn();
                    this._loading_message().fadeIn();
                    if (message) {
                        this._loading_message().html(message);
                    }
                }
                this._message_wrap().fadeOut();
                if (click) {
                    this._modal_and_message_wrap().unbind('click').click(function () {
                        click();
                        return false;
                    });
                }
            }

            show_loading() {
                this._loading().fadeIn();
            }

            show_message(message) {
                if (message) {
                    this.set_message(message);
                }
                this._message_wrap().show();
                const $this = this;
                let check_resize = function () {
                    if ($this._message_wrap().is(':visible')) {
                        $this._set_message_size();
                        setTimeout(check_resize, 1000);
                    }
                };
                check_resize();
            }

            hide() {
                this._modal().fadeOut();
                this.hide_loading();
                this.hide_message();
            }

            hide_loading() {
                this._loading().fadeOut();
                this._loading_message().fadeOut();
            }

            hide_message() {
                this._message_wrap().fadeOut();
            }

            set_message(message) {
                this._message().html(message);
                this._set_message_size();
            }

            _set_message_size() {
                const height = parseInt(this._message_wrap().get(0).offsetHeight / 2);
                this._message_wrap().css('margin-top', -height + 'px');
            }

        }

        window.<?php $instance->modal_class();?> = new <?php $instance->modal_class();?> ();
    })(jQuery);
</script>
