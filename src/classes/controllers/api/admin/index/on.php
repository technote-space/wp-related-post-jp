<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Changed: trivial change
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Api\Admin\Index;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class On
 * @package Related_Post\Classes\Controllers\Api\Admin\Index
 */
class On extends \WP_Framework_Api\Classes\Controllers\Api\Base {

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
		/** @var \Related_Post\Classes\Models\Control $control */
		$control = \Related_Post\Classes\Models\Control::get_instance( $this->app );

		return new \WP_REST_Response( [
			'result' => $control->on_posts_index(),
		] );
	}
}
