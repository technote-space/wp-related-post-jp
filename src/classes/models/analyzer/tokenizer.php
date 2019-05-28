<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.0.0.0
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
 * Class Tokenizer
 * @package Related_Post\Classes\Models\Analyzer
 */
abstract class Tokenizer implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

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
