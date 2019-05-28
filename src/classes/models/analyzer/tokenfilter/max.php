<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenfilter;

use Related_Post\Classes\Models\Analyzer\Tokenfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Max
 * @package Related_Post\Classes\Models\Analyzer\Tokenfilter
 */
class Max extends Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = [];
		foreach ( $terms as $word => $count ) {
			strlen( $word ) > 24 and $word = substr( $word, 0, 24 );
			$ret[ $word ] = ( isset( $ret[ $word ] ) ? $ret[ $word ] : 0 ) + $count;
		}

		return $ret;
	}

}
