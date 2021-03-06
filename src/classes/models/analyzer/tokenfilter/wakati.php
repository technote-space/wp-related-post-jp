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
 * Class Wakati
 * @package Related_Post\Classes\Models\Analyzer\Tokenfilter
 */
class Wakati extends Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = [];
		foreach ( $terms as $word => $count ) {
			if ( preg_match( '#\A\d+\z#', $word ) ) {
				continue;
			}
			if ( mb_strlen( $word ) === 1 && preg_match( '#[ぁ-んァ-ヶー\w]#', $word ) ) {
				// １文字のひらがな、カタカナ、英数字のみ
				continue;
			}
			if ( preg_match( '#\A[0-9 -/:;-@\[-`{-~.,！　”“＃＄％＆’（）＝～｜‘｛＋＊｝＜＞？＿－＾￥＠「；：」【】『』［］、〟。・★☆■□◆◇…―※]+\z#u', $word ) ) {
				// 半角数字記号、全角記号のみ
				continue;
			}
			$word         = mb_convert_kana( $word, 'c' );
			$word         = mb_strtolower( $word, 'utf-8' );
			$ret[ $word ] = ( isset( $ret[ $word ] ) ? $ret[ $word ] : 0 ) + $count;
		}

		return $ret;
	}

}
