<?php
/**
 * Technote Controller Admin Setting
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Setting
 * @package Technote\Controllers\Admin
 */
class Setting extends Base {

	/**
	 * @return int
	 */
	public function get_priority() {
		return $this->apply_filters( 'setting_page_priority', 0 );
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
	public function post_action() {
		foreach ( $this->app->setting->get_groups() as $group ) {
			foreach ( $this->app->setting->get_settings( $group ) as $setting ) {
				$this->app->option->set_post_value( \Technote\Models\Utility::array_get( $this->app->setting->get_setting( $setting, true ), 'name', '' ) );
			}
		}
		$this->app->add_message( 'Settings updated.', 'setting' );
	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		$settings = array();
		foreach ( $this->app->setting->get_groups() as $group ) {
			foreach ( $this->app->setting->get_settings( $group ) as $setting ) {
				$settings[ $group ][ $setting ] = $this->app->setting->get_setting( $setting, true );
			}
		}

		return array(
			'settings' => $settings,
		);
	}

	/**
	 * @return array
	 */
	protected function get_help_contents() {
		return array(
			array(
				'title' => 'ダッシュボードのヘルプの変更手順',
				'view'  => 'setting1',
			),
			array(
				'title' => 'このヘルプが不要な場合の手順',
				'view'  => 'setting2',
			),
			array(
				'title' => 'サイドバーの変更手順',
				'view'  => 'setting3',
			),
		);
	}

	/**
	 * @return false|string
	 */
	protected function get_help_sidebar() {
		if (
			! empty( $this->app->get_config( 'config', 'contact_url' ) ) ||
			! empty( $this->app->get_config( 'config', 'twitter' ) ) ||
			! empty( $this->app->get_config( 'config', 'github' ) )
		) {
			return 'setting';
		}

		return false;
	}

}
