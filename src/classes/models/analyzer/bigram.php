<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Bigram
 * @package Related_Post\Classes\Models\Analyzer
 */
class Bigram implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Core\Traits\Hook, \WP_Framework_Common\Traits\Package;

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function words( $text ) {
		// charfilter では行わない前処理

		// 改行等を除去
		$text = str_replace( [ ' ', '　', "\r", "\n", "\t" ], ' ', $text );

		$ret          = [];
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
		$ret   = [];
		foreach ( $words as $word ) {
			! isset( $ret[ $word ] ) and $ret[ $word ] = 0;
			$ret[ $word ] ++;
		}

		return $ret;
	}

}
