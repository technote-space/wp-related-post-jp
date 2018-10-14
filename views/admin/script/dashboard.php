<?php
/**
 * @version 1.0.2.3
 * @author technote-space
 * @since 1.0.2.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
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
            return false;
        }).eq(0).trigger('click');


    })(jQuery);
</script>
