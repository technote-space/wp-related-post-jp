<?php
/**
 * @version 1.2.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.2.0 Fixed: code remove regular expression
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Code
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Code extends \Related_Post\Classes\Models\Analyzer\Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		return preg_replace( '#<pre[\s>].+?</pre>#s', ' ', $text );
	}

}
