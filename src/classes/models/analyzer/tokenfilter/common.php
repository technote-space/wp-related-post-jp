<?php
/**
 * @author Technote
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
 * Class Common
 * @package Related_Post\Classes\Models\Analyzer\Tokenfilter
 */
class Common extends Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = [];
		foreach ( $terms as $word => $count ) {
			$word = trim( $word );
			if ( "" === $word ) {
				continue;
			}
			$ret[ $word ] = ( isset( $ret[ $word ] ) ? $ret[ $word ] : 0 ) + $count;
		}

		return $ret;
	}

}
