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
 * Class Title_Content
 * @package Related_Post\Classes\Models\Analyzer\Extractor
 */
class Title_Content extends Extractor {

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return str_repeat( $post->post_title . ' ', $this->apply_filters( 'title_weight', 3 ) ) . $post->post_content;
	}

}
