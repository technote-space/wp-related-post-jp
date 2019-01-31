<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.2.1
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Admin;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Dashboard
 * @package Related_Post\Classes\Controllers\Admin
 */
class Dashboard extends \WP_Framework_Admin\Classes\Controllers\Admin\Base {

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

	/**
	 * post
	 */
	protected function post_action() {
		$exclude_categories = $this->app->input->post( 'exclude_categories' );
		! is_array( $exclude_categories ) and $exclude_categories = [];
		$this->app->input->set_post( $this->get_filter_prefix() . 'exclude_categories', implode( ',', $exclude_categories ) );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'ranking_number' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'related_posts_title' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'auto_insert_related_post', 0 );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'exclude_categories' );
		$this->app->add_message( 'Settings updated.', 'setting' );
	}

	/**
	 * common
	 */
	protected function common_action() {
		$this->app->api->add_use_api_name( 'word_get' );
		$this->app->api->add_use_api_name( 'word_on' );
		$this->app->api->add_use_api_name( 'word_off' );
	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		/** @var \Related_Post\Classes\Models\Control $control */
		$control = \Related_Post\Classes\Models\Control::get_instance( $this->app );

		return [
			'tabs'                     => [
				'basic'   => 'Basic Settings',
				'exclude' => 'Exclude Settings',
				'insert'  => 'Auto Insert Settings',
			],
			'admin_page_url'           => admin_url( 'admin.php?page=' ),
			'ranking_number'           => $this->get_setting( 'ranking_number' ),
			'related_posts_title'      => $this->get_setting( 'related_posts_title' ),
			'auto_insert_related_post' => $this->get_setting( 'auto_insert_related_post', true ),
			'category_data'            => $control->get_category_data(),
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

	/**
	 * @return array
	 */
	protected function get_help_contents() {
		return [
			'title' => 'Customize',
			'view'  => 'dashboard',
		];
	}
}
