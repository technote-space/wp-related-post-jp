<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Api\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Progress
 * @package Related_Post\Controllers\Api\Admin
 */
class Progress extends \Technote\Controllers\Api\Base {

	/**
	 * @return string
	 */
	public function get_endpoint() {
		return 'progress';
	}

	/**
	 * @return string
	 */
	public function get_call_function_name() {
		return 'progress';
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
		$posts_indexed        = ! empty( $this->app->get_option( 'posts_indexed' ) );
		$is_valid_posts_index = ! empty( $this->app->get_option( 'is_valid_posts_index' ) );
		$total                = 0;
		$target               = 0;
		$processed            = 0;
		$processed_rate       = 0;
		$next                 = '';
		if ( ! $posts_indexed && $is_valid_posts_index ) {
			/** @var \Related_Post\Models\Control $control */
			$control        = \Related_Post\Models\Control::get_instance( $this->app );
			$total          = $control->get_total_posts_count();
			$target         = $control->get_update_posts_count();
			$processed      = $total - $target;
			$processed_rate = ceil( $processed * 100 / $total );
			list( $next, $process ) = $control->get_interval_lock_process();
			if ( $next < 0 ) {
				if ( ! empty( $process ) ) {
					$next = sprintf( $this->app->translate( 'Running %s...' ), $this->app->translate( $process ) );
				} else {
					$next = $this->app->translate( 'Waiting...' );
				}
			} else {
				$next = sprintf( $this->app->translate( 'Next execute: %s sec later' ), $next );
			}
		}

		return new \WP_REST_Response( [
			'posts_indexed'        => $posts_indexed,
			'is_valid_posts_index' => $is_valid_posts_index,
			'total'                => $total,
			'target'               => $target,
			'processed'            => $processed,
			'processed_rate'       => $processed_rate,
			'next'                 => $next,
		] );
	}
}
