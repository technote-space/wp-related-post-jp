<?php
/**
 * @version 1.0.0.0
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
 * Class Bigram
 * @package Related_Post\Models\Analyzer\Tokenfilter
 */
class Bigram extends \Related_Post\Models\Analyzer\Tokenfilter {

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public function filter( $terms ) {
		$ret = [];
		foreach ( $terms as $word => $count ) {
			if ( preg_match( '#[！”“＃＄％＆’（）＝～｜‘｛＋＊｝＜＞？＿－＾￥＠「；：」【】『』［］、〟。・★☆■□◆◇…]+#u', $word ) ) {
				//　全角記号を含む
				continue;
			}
			if ( preg_match( '#[ -/:;-@\[-\`\{-\~]+#', $word ) ) {
				// 半角記号を含む
				continue;
			}
			$ret[ $word ] = $count;
		}

		return $ret;
	}

}
