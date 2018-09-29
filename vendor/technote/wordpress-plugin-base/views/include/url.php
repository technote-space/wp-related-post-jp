<?php
/**
 * Technote Views Include Img
 *
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
/** @var \Technote\Traits\Presenter $instance */
/** @var array $args */
/** @var string $id */
/** @var string $class */
/** @var string $href */
/** @var string $target */
/** @var string $contents */
/** @var array $attributes */
empty( $attributes ) and $attributes = array();
isset( $id ) and $attributes['id'] = $id;
isset( $class ) and $attributes['class'] = $class;
$attributes['href'] = $href;
isset( $target ) and $attributes['target'] = $target;
! isset( $contents ) and $contents = '';
?>
<a <?php $instance->get_view( 'include/attributes', array_merge( $args, array( 'attributes' => $attributes ) ), true ); ?> >
	<?php echo $contents ?>
</a>