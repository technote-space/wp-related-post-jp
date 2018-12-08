<?php
/**
 * @version 1.0.3.3
 * @author technote-space
 * @since 1.0.2.1
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Dashboard
 * @package Related_Post\Classes\Controllers\Admin
 */
class Dashboard extends \Technote\Classes\Controllers\Admin\Base {

	/**
	 * @return int
	 */
	public function get_load_priority() {
		return 0;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Dashboard';
	}

	protected function post_action() {
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'ranking_number' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'related_posts_title' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'auto_insert_related_post', 0 );
		$this->app->add_message( 'Settings updated.', 'setting' );
	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		return [
			'admin_page_url'           => admin_url( 'admin.php?page=' ),
			'ranking_number'           => $this->get_setting( 'ranking_number' ),
			'related_posts_title'      => $this->get_setting( 'related_posts_title' ),
			'auto_insert_related_post' => $this->get_setting( 'auto_insert_related_post', true ),
		];
	}

	/**
	 * @param string $name
	 * @param bool $checkbox
	 * @param null|callable $callback
	 *
	 * @return array
	 */
	private function get_setting( $name, $checkbox = false, $callback = null ) {
		$value = $this->app->setting->get_setting( $name, true )['value'];
		if ( is_callable( $callback ) ) {
			$value = $callback( $value, $name );
		}

		$ret = [
			'id'         => $name,
			'name'       => $this->get_filter_prefix() . $name,
			'value'      => $value,
			'class'      => 'check-value-changed',
			'attributes' => [
				'data-value' => $value,
			],
		];
		if ( $checkbox ) {
			$ret['value'] = 1;
			$ret['class'] = 'check-checked-changed';
			! empty( $value ) and $ret['attributes']['checked'] = 'checked';
		}

		return $ret;
	}
}
