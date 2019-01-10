<?php
/**
 * @version 1.2.6
 * @author technote-space
 * @since 1.2.6
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
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
		return preg_replace( '#<!--[\s\S]*?-->#', '', $text );
	}

}