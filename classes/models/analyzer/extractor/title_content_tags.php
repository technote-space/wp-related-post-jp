<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Extractor;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Title_content_tags
 * @package Related_Post\Models\Analyzer\Extractor
 */
class Title_content_tags extends \Related_Post\Models\Analyzer\Extractor {

	/**
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return str_repeat( $post->post_title . ' ', 10 ) . $post->post_content . ' ' . str_repeat( implode( ' ', wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ) ), 15 );
	}

}