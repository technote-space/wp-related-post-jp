<?php
/**
 * @version 1.1.3
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Tokenfilter
 * @package Related_Post\Classes\Models\Analyzer
 */
abstract class Tokenfilter implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/**
	 * @param array $terms ( word => count )
	 *
	 * @return array ( word => count )
	 */
	public abstract function filter( $terms );

}