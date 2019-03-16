<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.2.3 Updated: setup to use api
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Controllers\Admin;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Progress
 * @package Related_Post\Classes\Controllers\Admin
 */
class Progress extends \WP_Framework_Admin\Classes\Controllers\Admin\Base {

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Progress';
	}

	/**
	 * common
	 */
	protected function common_action() {
		$this->app->api->set_use_all_api_flag( true );
	}
}
