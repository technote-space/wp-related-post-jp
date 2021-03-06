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
 * Class Bigram
 * @package Related_Post\Classes\Models\Analyzer\Tokenfilter
 */
class Bigram extends Tokenfilter {

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
			if ( preg_match( '#[ -/:;-@\[-`{-~]+#', $word ) ) {
				// 半角記号を含む
				continue;
			}
			$ret[ $word ] = $count;
		}

		return $ret;
	}

}
