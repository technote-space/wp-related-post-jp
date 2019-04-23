<?php
/**
 * @version 1.3.14
 * @author Technote
 * @since 1.0.2.1
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

	use \WP_Framework_Admin\Traits\Dashboard;

	/**
	 * @return array
	 */
	protected function get_setting_list() {
		return [
			'ranking_number',
			'ranking_threshold',
			'search_threshold',
			'related_posts_title',
			'auto_insert_related_post',
			'exclude_categories',
			'exclude_ids',
			'use_admin_ajax',
		];
	}

	/**
	 * @return array
	 */
	protected function get_tabs() {
		return [
			'basic'   => [
				'name'  => 'Basic Settings',
				'items' => [
					'related_posts_title',
					'ranking_number',
				],
			],
			'exclude' => [
				'name'  => 'Exclude Settings',
				'items' => [
					'ranking_threshold',
					'search_threshold',
				],
			],
			'misc'    => [
				'name'  => 'Misc',
				'items' => [
					'auto_insert_related_post',
					'use_admin_ajax',
				],
			],
		];
	}

	/**
	 * before update
	 */
	protected function before_update() {
		$exclude_categories = $this->app->input->post( 'exclude_categories' );
		! is_array( $exclude_categories ) and $exclude_categories = [];
		$this->app->input->set_post( $this->get_filter_prefix() . 'exclude_categories', implode( ',', $exclude_categories ) );

		$exclude_ids = $this->app->array->filter( $this->app->string->explode( $this->app->input->post( 'exclude_ids' ), [ ',', ' ' ] ), function ( $id ) {
			return ctype_digit( $id ) && (int) $id > 0;
		} );
		$this->app->input->set_post( $this->get_filter_prefix() . 'exclude_ids', implode( ',', $exclude_ids ) );
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
	 * @param array $args
	 *
	 * @return array
	 */
	protected function filter_view_args( array $args ) {
		$args['admin_page_url']  = admin_url( 'admin.php?page=' );
		$args['no_reset_button'] = true;

		/** @var \Related_Post\Classes\Models\Control $control */
		$control                  = \Related_Post\Classes\Models\Control::get_instance( $this->app );
		$args['category_data']    = $control->get_category_data();
		$args['exclude_post_ids'] = $this->app->string->implode( $control->get_exclude_post_ids(), ', ' );

		return $args;
	}

	/**
	 * @param array $detail
	 * @param string $name
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_detail(
		/** @noinspection PhpUnusedParameterInspection */
		$detail, $name, array $option
	) {
		$detail['class']                    = 'check-value-changed';
		$detail['attributes']['data-value'] = $detail['value'];

		return $detail;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $detail
	 * @param array $option
	 *
	 * @return array
	 */
	protected function filter_type_setting(
		/** @noinspection PhpUnusedParameterInspection */
		$name, $type, array $detail, array $option
	) {
		if ( 'bool' === $type ) {
			$detail['class'] = 'check-checked-changed';
		}

		return $detail;
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
