<?php
/**
 * @version 1.0.2.5
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Api\Admin\Index;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class On
 * @package Related_Post\Controllers\Api\Admin
 */
class On extends \Technote\Controllers\Api\Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'index_on';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'index_on';
	}

	/**
	 * @return string
	 */
	public function get_method() {
		return 'post';
	}

	/**
	 * @return null|string|false
	 */
	public function get_capability() {
		return null;
	}

	/**
	 * @return bool
	 */
	public function is_only_admin() {
		return true;
	}

	/**
	 * @param \WP_REST_Request|array $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var \Related_Post\Models\Control $control */
		$control = \Related_Post\Models\Control::get_instance( $this->app );

		return new \WP_REST_Response( [
			'result' => $control->on_posts_index(),
		] );
	}
}
