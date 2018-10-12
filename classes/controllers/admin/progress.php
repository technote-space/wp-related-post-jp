<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
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
}
