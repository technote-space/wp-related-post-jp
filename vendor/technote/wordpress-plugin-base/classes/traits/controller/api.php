<?php
/**
 * Technote Traits Controller Api
 *
 * @version 1.1.21
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits\Controller;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Api
 * @package Technote\Traits\Controller
 * @property \Technote $app
 */
trait Api {

	use \Technote\Traits\Controller;

	/**
	 * @return string
	 */
	public abstract function get_endpoint();

	/**
	 * @return string
	 */
	public abstract function get_call_function_name();

	/**
	 * @return string
	 */
	public abstract function get_method();

	/**
	 * @return array
	 */
	public function get_args_setting() {
		return [];
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_only_admin() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function is_only_front() {
		return false;
	}

	/**
	 * @return false|string
	 */
	public function common_script() {
		return false;
	}

	/**
	 * @return false|string
	 */
	public function admin_script() {
		return $this->common_script();
	}

	/**
	 * @return false|string
	 */
	public function front_script() {
		return $this->common_script();
	}

	/**
	 * @param \WP_REST_Request|array $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback(
		/** @noinspection PhpUnusedParameterInspection */
		$params
	) {
		return new \WP_REST_Response( null, 404 );
	}

}
