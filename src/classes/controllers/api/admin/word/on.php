<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Api\Admin\Word;

use Related_Post\Classes\Models\Update;
use WP_Error;
use WP_Framework_Api\Classes\Controllers\Api\Base;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class On
 * @package Related_Post\Classes\Controllers\Api\Admin\Word
 */
class On extends Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'word_on';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'word_on';
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
	 * @return array
	 */
	public function get_args_setting() {
		return [
			'word' => [
				'required'          => true,
				'description'       => 'word',
				'validate_callback' => function ( $var ) {
					return ! empty( $var );
				},
				'sanitize_callback' => function ( $var ) {
					return trim( $var );
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
		return $this->get_view( 'admin/api/word_on' );
	}

	/**
	 * @param WP_REST_Request|array $params
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var Update $update */
		$update = Update::get_instance( $this->app );

		return new WP_REST_Response( [
			'result' => $update->off_exclude_word( $params['word'] ),
		] );
	}
}
