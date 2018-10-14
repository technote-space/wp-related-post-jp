<?php
/**
 * @version 1.0.2.3
 * @author technote-space
 * @since 1.0.2.1
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Dashboard
 * @package Related_Post\Controllers\Admin
 */
class Dashboard extends \Technote\Controllers\Admin\Base {

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Dashboard';
	}

	public function post_action() {
		$this->app->add_message( 'Settings updated.', 'setting' );
	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		return [
			'admin_page_url' => admin_url( 'admin.php?page=' ),
			'ranking_number' => $this->get_setting( 'ranking_number' ),
		];
	}

	/**
	 * @param $name
	 * @param null|callable $callback
	 *
	 * @return array
	 */
	private function get_setting( $name, $callback = null ) {
		$value = $this->app->setting->get_setting( $name, true )['used'];
		if ( is_callable( $callback ) ) {
			$value = $callback( $value );
		}

		return [
			'id'    => $name,
			'name'  => $this->get_filter_prefix() . $name,
			'value' => $value,
		];
	}
}
