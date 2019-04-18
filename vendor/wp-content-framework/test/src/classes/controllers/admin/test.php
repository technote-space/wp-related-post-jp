<?php
/**
 * WP_Framework_Test Classes Controller Test
 *
 * @version 0.0.13
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Test\Classes\Controllers\Admin;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Test
 * @package WP_Framework_Test\Classes\Controllers\Admin
 */
class Test extends \WP_Framework_Admin\Classes\Controllers\Admin\Base {

	use \WP_Framework_Test\Traits\Package;

	/**
	 * @return int
	 */
	public function get_load_priority() {
		return $this->app->test->is_valid() ? $this->apply_filters( 'test_page_priority', $this->app->utility->defined( 'WP_DEBUG' ) ? 900 : - 1 ) : - 1;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {
		return $this->apply_filters( 'test_page_title', 'Test' );
	}

	/**
	 * post
	 */
	protected function post_action() {
		$action = $this->app->input->post( 'action' );
		if ( $this->is_method_callable( $action ) ) {
			call_user_func( [ $this, $action ] );
		}
	}

	/**
	 * @return array
	 */
	public function get_view_args() {
		return [
			'tests' => $this->app->test->get_test_class_names(),
		];
	}

	/**
	 * do test
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function do_test() {
		foreach ( $this->app->test->do_tests() as list( $success, $result ) ) {
			$this->app->add_message( $result, 'test', ! $success, false, [
				'table' => [ 'class' => true ],
				'tr'    => [],
				'td'    => [],
				'ul'    => [],
				'li'    => [],
			] );
		}
	}
}
