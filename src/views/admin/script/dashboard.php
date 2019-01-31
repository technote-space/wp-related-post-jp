<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.2.3
 * @since 1.3.0 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
?>

<script>
    (function ($) {
        $('#<?php $instance->id(); ?>-dashboard .nav-tab').click(function () {
            const page = $(this).data('target_page');
            if (page) {
                location.href = $(this).closest('h2').data('admin_page_url') + page;
                return false;
            }
            $('#<?php $instance->id(); ?>-dashboard .nav-tab').removeClass('nav-tab-active');
            $('#<?php $instance->id(); ?>-dashboard .<?php $instance->id(); ?>-tab-content').removeClass('active');
            $(this).addClass('nav-tab-active');
            $('.<?php $instance->id(); ?>-tab-content[data-tab="' + $(this).data('target') + '"]').addClass('active');
            location.hash = $(this).data('target');
            const action = $(this).closest('form').attr('action').replace(/#\w+$/, '') + '#' + $(this).data('target');
            $(this).closest('form').attr('action', action);
            return false;
        });

        const hash = location.hash;
        let tab;
        if (hash) {
            tab = $('[data-target="' + location.hash.replace(/^#/, '') + '"]').eq(0);
            if (tab.length <= 0) tab = null;
        }
        if (!tab) {
            tab = $('#<?php $instance->id(); ?>-dashboard .nav-tab').eq(0);
        }
        tab.trigger('click');

        const check_changed = function () {
            let result = false;
            $('.check-value-changed').each(function () {
                result |= $(this).val() + '' != $(this).data('value') + '';
            });
            $('.check-checked-changed').each(function () {
                result = $(this).prop('checked') != $(this).data('value');
            });
            return result;
        };
        $(window).on('submit', function () {
            $(window).off('beforeunload');
        });
        $(window).on('beforeunload', function () {
            if (check_changed()) {
                return '<?php $instance->h( 'Are you sure you want to discard the changes?' );?>';
            }
        });
    })(jQuery);
</script>
