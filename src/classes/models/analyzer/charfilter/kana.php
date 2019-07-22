<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

use Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Kana
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Kana extends Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		// 半角カタカナを全角カタカナに変換
		// 全角英数字を半角英数字に変換
		return mb_convert_kana( $text, 'aK' );
	}

}
