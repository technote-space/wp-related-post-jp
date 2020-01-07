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
 * Class Clear
 * @package Related_Post\Classes\Controllers\Api\Admin\Index
 */
class Clear extends Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'index_clear';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'index_clear';
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
	 * @param WP_REST_Request|array $params
	 *
	 * @return int|WP_Error|WP_REST_Response
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function callback( $params ) {
		/** @var Update $update */
		$update = Update::get_instance( $this->app );

		return new WP_REST_Response( [
			'result' => $update->init_posts_index(),
		] );
	}
}
