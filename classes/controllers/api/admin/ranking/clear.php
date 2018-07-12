<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Api\Admin\Ranking;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Clear
 * @package Related_Post\Controllers\Api\Admin\Ranking
 */
class Clear extends \Technote\Controllers\Api\Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'ranking_clear';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'ranking_clear';
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
	 * @param \WP_REST_Request $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( \WP_REST_Request $params ) {
		return new \WP_REST_Response( array(
			'result' => \Related_Post\Models\Control::get_instance( $this->app )->clear_ranking(),
		) );
	}
}
