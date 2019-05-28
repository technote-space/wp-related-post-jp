<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
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
 * Class Content
 * @package Related_Post\Classes\Models\Analyzer\Extractor
 */
class Content extends Extractor {

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return $post->post_content;
	}

}
