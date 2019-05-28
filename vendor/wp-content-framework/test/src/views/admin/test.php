<?php
/**
 * WP_Framework_Test Views Admin Test
 *
 * @version 0.0.14
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var array $args */
/** @var array $tests */
$instance->add_style_view( 'admin/style/table' );
?>

<h3><?php $instance->h( 'Test count: %d', true, true, true, count( $tests ) ); ?></h3>
<table class="widefat striped">
	<?php foreach ( $tests as $test ): ?>
        <tr>
            <td>
				<?php $instance->h( $test ); ?>
            </td>
        </tr>
	<?php endforeach; ?>
</table>
<?php $instance->form( 'open' ); ?>
<?php $instance->form( 'input/hidden', [
	'name'  => 'action',
	'value' => 'do_test',
] ); ?>
<?php $instance->form( 'input/submit', $args, [
	'name'  => 'execute',
	'value' => 'Execute',
	'class' => 'button-primary',
] ); ?>
<?php $instance->form( 'close' ); ?>
