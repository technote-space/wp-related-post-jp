<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Api\Admin;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Progress
 * @package Related_Post\Classes\Controllers\Api\Admin
 */
class Progress extends \WP_Framework_Api\Classes\Controllers\Api\Base {

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
	 * @param \WP_REST_Request|array $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( $params ) {
		/** @var \Related_Post\Classes\Models\Control $control */
		$control              = \Related_Post\Classes\Models\Control::get_instance( $this->app );
		$posts_indexed        = ! empty( $control->cache_get( 'posts_indexed' ) );
		$is_valid_posts_index = $control->is_valid_posts_index();
		$total                = 0;
		$target               = 0;
		$processed            = 0;
		$processed_rate       = 0;
		$next                 = '';
		if ( ! $posts_indexed && $is_valid_posts_index ) {
			$total          = $control->get_total_posts_count();
			$target         = $control->get_update_posts_count();
			$processed      = $total - $target;
			$processed_rate = ceil( $processed * 100 / $total );
			list( $next, $process ) = $control->get_interval_lock_process();
			if ( $next <= 0 ) {
				if ( ! empty( $process ) ) {
					$next = sprintf( $this->translate( 'Running %s...' ), $this->translate( $process ) );
				} else {
					$next = $this->translate( 'Waiting...' );
				}
			} else {
				$next = sprintf( $this->translate( 'Next execute: %s sec later' ), $next );
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
