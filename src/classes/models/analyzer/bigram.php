<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Bigram
 * @package Related_Post\Classes\Models\Analyzer
 */
class Bigram implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function words( $text ) {
		$data = [];
		foreach ( explode( ' ', str_replace( [ ' ', '　', "\r", "\n", "\t" ], ' ', $text ) ) as $value ) {
			$array = preg_split( '//u', $value, -1, PREG_SPLIT_NO_EMPTY );
			$end   = count( $array ) - 2;
			if ( $end < 0 ) {
				continue;
			}

			foreach ( range( 0, $end ) as $idx ) {
				$data[] = $array[ $idx ] . $array[ $idx + 1 ];
			}
		}

		return $data;
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function count( $text ) {
		$words = $this->words( $text );
		$ret   = [];
		foreach ( $words as $word ) {
			if ( ! isset( $ret[ $word ] ) ) {
				$ret[ $word ] = 0;
			}
			$ret[ $word ]++;
		}

		return $ret;
	}

}
