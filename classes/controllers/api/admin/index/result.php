<?php
/**
 * @version 1.0.2.5
 * @author technote-space
 * @since 1.0.1.9
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Api\Admin\Index;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Result
 * @package Related_Post\Controllers\Api\Admin\Index
 */
class Result extends \Technote\Controllers\Api\Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'wrpj_index_result';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'wrpj_index_result';
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
				'required'            => true,
				'description'         => 'post_id',
				'validation_callback' => function ( $var ) {
					return ! empty( $var );
				},
				'sanitize_callback'   => function ( $var ) {
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
	 * @param \WP_REST_Request|array $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var \Related_Post\Models\Control $control */
		$control = \Related_Post\Models\Control::get_instance( $this->app );

		return new \WP_REST_Response( $control->get_index_result_response( $params['p'] ) );
	}
}
