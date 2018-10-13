<?php
/**
 * @version 1.0.1.9
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Tokenfilter;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Wakati
 * @package Related_Post\Models\Analyzer\Tokenfilter
 */
class Wakati extends \Related_Post\Models\Analyzer\Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = [];
		foreach ( $terms as $word => $count ) {
			if ( preg_match( '#^\d+$#', $word ) ) {
				continue;
			}
			if ( mb_strlen( $word ) === 1 && preg_match( '#[ぁ-んァ-ヶー\w]#', $word ) ) {
				// １文字のひらがな、カタカナ、英数字のみ
				continue;
			}
			if ( preg_match( '#^[0-9 -/:;-@\[-\`\{-\~.,！　”“＃＄％＆’（）＝～｜‘｛＋＊｝＜＞？＿－＾￥＠「；：」【】『』［］、〟。・★☆■□◆◇…―※]+$#u', $word ) ) {
				// 半角数字記号、全角記号のみ
				continue;
			}
			$word         = mb_convert_kana( $word, 'c' );
			$word         = mb_strtolower( $word, "utf-8" );
			$ret[ $word ] = $count;
		}

		return $ret;
	}

}
