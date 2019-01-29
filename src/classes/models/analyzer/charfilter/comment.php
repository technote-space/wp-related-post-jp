<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.2.6
 * @since 1.3.0 Changed: 除去後に空白追加 (#33)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Comment
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Comment extends \Related_Post\Classes\Models\Analyzer\Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		return preg_replace( '#<!--[\s\S]*?-->#', ' ', $text );
	}

}
