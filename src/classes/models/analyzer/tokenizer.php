<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Tokenizer
 * @package Related_Post\Classes\Models\Analyzer
 */
abstract class Tokenizer implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Core\Traits\Hook, \WP_Framework_Common\Traits\Package;

	/**
	 * @param string $text
	 *
	 * @return array ( word => count )
	 */
	public abstract function parse( $text );

	/**
	 * @return bool
	 */
	public function is_valid() {
		return true;
	}

}
