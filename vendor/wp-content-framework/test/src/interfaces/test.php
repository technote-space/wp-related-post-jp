<?php
/**
 * WP_Framework_Test Interfaces Test
 *
 * @version 0.0.1
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Test\Interfaces;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Interface Test
 * @package WP_Framework_Test\Interfaces
 */
interface Test extends \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	/**
	 * @return string
	 */
	public function get_test_slug();

	/**
	 * @return bool
	 */
	public function has_dump_objects();

	/**
	 * @return array
	 */
	public function get_dump_objects();

}
