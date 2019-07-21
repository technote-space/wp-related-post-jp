<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Extractor;

use Related_Post\Classes\Models\Analyzer\Extractor;
use WP_Post;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Title_Content_Tags
 * @package Related_Post\Classes\Models\Analyzer\Extractor
 */
class Title_Content_Tags extends Extractor {

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return str_repeat( $post->post_title . ' ', 3 ) . str_repeat( implode( ' ', wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] ) ) . ' ', 5 ) . $post->post_content;
	}

}
