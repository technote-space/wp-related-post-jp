<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Extractor;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Title
 * @package Related_Post\Models\Analyzer\Extractor
 */
class Title extends \Related_Post\Models\Analyzer\Extractor {

	/**
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public function extract( $post ) {
		return $post->post_title;
	}

}
