<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.3.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Reference
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Reference extends \Related_Post\Classes\Models\Analyzer\Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		return preg_replace( '/&#?[a-z0-9]{2,8};/i', ' ', $text );
	}

}
