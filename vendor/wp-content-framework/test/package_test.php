<?php
/**
 * WP_Framework Package Test
 *
 * @version 0.0.5
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Package_Test
 * @package WP_Framework
 */
class Package_Test extends Package_Base {

	/**
	 * @return int
	 */
	public function get_priority() {
		return 50;
	}

	/**
	 * @return bool
	 */
	protected function is_valid_view() {
		return true;
	}

	/**
	 * @return bool
	 */
	protected function is_valid_translate() {
		return true;
	}

	/**
	 * @return array
	 */
	public function get_configs() {
		return [
			'map',
		];
	}
}
