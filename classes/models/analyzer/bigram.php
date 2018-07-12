<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Bigram
 * @package Related_Post\Models\Analyzer
 */
class Bigram implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function words( $text ) {
		// charfilter では行わない前処理

		// 改行等を除去
		$text = str_replace( array( ' ', '　', "\r", "\n", "\t" ), ' ', $text );

		$ret          = array();
		$prev         = null;
		$alpha        = false;
		$alpha_buffer = '';
		foreach ( preg_split( '//u', $text, - 1, PREG_SPLIT_NO_EMPTY ) as $item ) {
			if ( ctype_alpha( $item ) ) {
				$alpha        = true;
				$alpha_buffer .= $item;
				$prev         = null;
				continue;
			} else {
				if ( $alpha ) {
					if ( ! empty( $alpha_buffer ) ) {
						$ret[]        = $alpha_buffer;
						$alpha_buffer = '';
					}
					$alpha = false;
				}
			}
			if ( isset( $prev ) ) {
				$ret[] = $prev . $item;
			}
			$prev = $item;
		}
		if ( $alpha && ! empty( $alpha_buffer ) ) {
			$ret[] = $alpha_buffer;
		}

		return $ret;
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function count( $text ) {
		$words = $this->words( $text );
		$ret   = array();
		foreach ( $words as $word ) {
			! isset( $ret[ $word ] ) and $ret[ $word ] = 0;
			$ret[ $word ] ++;
		}

		return $ret;
	}

}
