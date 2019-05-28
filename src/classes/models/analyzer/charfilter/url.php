<?php
/**
 * @version 1.3.9
 * @author Technote
 * @since 1.3.9
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

use Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Url
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Url extends Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		return preg_replace( '/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/', '', $text );
	}

}
