<?php
/**
 * @version 1.3.9
 * @author Technote
 * @since 1.0.2.1
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Added: 除外カテゴリ (#12)
 * @since 1.3.2 Added: 除外ワード (#22)
 * @since 1.3.9 #51, wp-content-framework/admin#20
 * @copyright Technote All Rights Reserved
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

		$exclude_ids = $this->app->array->filter( $this->app->string->explode( $this->app->input->post( 'exclude_ids' ), [ ',', ' ' ] ), function ( $id ) {
			return ctype_digit( $id ) && (int) $id > 0;
		} );
		$this->app->input->set_post( $this->get_filter_prefix() . 'exclude_ids', implode( ',', $exclude_ids ) );

		$this->app->option->set_post_value( $this->get_filter_prefix() . 'ranking_number' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'ranking_threshold' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'search_threshold' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'related_posts_title' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'auto_insert_related_post', 0 );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'exclude_categories' );
		$this->app->option->set_post_value( $this->get_filter_prefix() . 'exclude_ids' );
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
			'ranking_threshold'        => $this->get_setting( 'ranking_threshold' ),
			'search_threshold'         => $this->get_setting( 'search_threshold' ),
			'related_posts_title'      => $this->get_setting( 'related_posts_title' ),
			'auto_insert_related_post' => $this->get_setting( 'auto_insert_related_post' ),
			'category_data'            => $control->get_category_data(),
			'exclude_post_ids'         => $this->app->string->implode( $control->get_exclude_post_ids(), ', ' ),
			'no_reset_button'          => true,
		];
	}

	/**
	 * @param string $name
	 * @param null|callable $callback
	 *
	 * @return array
	 */
	private function get_setting( $name, $callback = null ) {
		$setting = $this->app->setting->get_setting( $name, true );
		$value   = $setting['value'];
		if ( is_callable( $callback ) ) {
			$value = $callback( $value, $name );
		}

		$ret  = [
			'id'         => $name,
			'name'       => $this->get_filter_prefix() . $name,
			'value'      => $value,
			'class'      => 'check-value-changed',
			'attributes' => [
				'data-value' => $value,
			],
		];
		$type = $this->app->array->get( $setting, 'type' );
		if ( 'bool' === $type ) {
			$ret['value'] = 1;
			$ret['class'] = 'check-checked-changed';
			! empty( $value ) and $ret['attributes']['checked'] = 'checked';
		} elseif ( 'float' === $type ) {
			$ret['attributes']['step'] = '0.01';
		}
		if ( 'int' === $type || 'float' === $type ) {
			$min = $this->app->array->get( $setting, 'min' );
			$max = $this->app->array->get( $setting, 'max' );
			isset( $min ) and $ret['attributes']['min'] = $min;
			isset( $max ) and $ret['attributes']['max'] = $max;
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
