<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.3.2
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Api\Admin\Word;

use Related_Post\Classes\Models\Control;
use WP_Error;
use WP_Framework_Api\Classes\Controllers\Api\Base;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Get
 * @package Related_Post\Classes\Controllers\Api\Admin\Word
 */
class Get extends Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'word_get';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'word_get';
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
			'per_page' => [
				'required'          => false,
				'description'       => 'per page number',
				'default'           => 50,
				'validate_callback' => function ( $var ) {
					return $this->validate_int( $var );
				},
				'sanitize_callback' => function ( $var ) {
					return (int) $var;
				},
			],
			'page'     => [
				'required'          => false,
				'description'       => 'page',
				'default'           => 1,
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
	 * @param WP_REST_Request|array $params
	 *
	 * @return int|WP_Error|WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var Control $control */
		$control = Control::get_instance( $this->app );
		list( $words, $has_next ) = $control->get_excluded_words( $params['page'], $params['per_page'] );

		return new WP_REST_Response( [
			'words'    => $words,
			'has_next' => $has_next,
		] );
	}
}
