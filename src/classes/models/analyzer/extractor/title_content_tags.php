<?php
/**
 * @version 1.2.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.1.4 Fixed: tag's priority
 * @since 1.2.0 Changed: weighting
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Extractor;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Title_content_tags
 * @package Related_Post\Classes\Models\Analyzer\Extractor
 */
class Title_content_tags extends \Related_Post\Classes\Models\Analyzer\Extractor {

	/**
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return str_repeat( $post->post_title . ' ', 3 ) . ' ' . str_repeat( implode( ' ', wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ) ), 5 ) . $post->post_content;
	}

}
