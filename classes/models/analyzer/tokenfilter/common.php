<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Tokenfilter;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Common
 * @package Related_Post\Models\Analyzer\Tokenfilter
 */
class Common extends \Related_Post\Models\Analyzer\Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = array();
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
