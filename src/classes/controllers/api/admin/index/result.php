<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Api\Admin\Index;

use Related_Post\Classes\Models\Update;
use WP_Error;
use WP_Framework_Api\Classes\Controllers\Api\Base;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Result
 * @package Related_Post\Classes\Controllers\Api\Admin\Index
 */
class Result extends Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'index_result';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'index_result';
	}

	/**
	 * @return string
	 */
	public function get_method() {
		return 'get';
	}

	/**
	 * @return null|string|false
	 */
	public function get_capability() {
		return null;
	}

	/**
	 * @return array
	 */
	public function get_args_setting() {
		return [
			'p' => [
				'required'          => true,
				'description'       => 'post_id',
				'validate_callback' => function ( $var ) {
					return $this->validate_positive_int( $var );
				},
				'sanitize_callback' => function ( $var ) {
					return (int) $var;
				},
			],
		];
	}

	/**
	 * @return bool
	 */
	public function is_only_admin() {
		return true;
	}

	/**
	 * @return string
	 */
	public function admin_script() {
		return $this->get_view( 'admin/api/index_result' );
	}

	/**
	 * @param WP_REST_Request|array $params
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var Update $update */
		$update = Update::get_instance( $this->app );

		return new WP_REST_Response( $update->get_index_result_response( $params['p'] ) );
	}
}
