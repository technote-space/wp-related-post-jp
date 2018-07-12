<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Progress
 * @package Related_Post\Controllers\Admin
 */
class Progress extends \Technote\Controllers\Admin\Base {

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Progress';
	}

	/**
	 * admin enqueue scripts
	 */
	protected function admin_enqueue_scripts() {
		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_style( 'jquery-ui-progressbar', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css" );
	}

}
