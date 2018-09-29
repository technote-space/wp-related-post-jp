<?php
/**
 * Technote Traits Controller
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Controller
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Controller {

	use Singleton, Presenter;

	/**
	 * @return bool
	 */
	protected function is_post() {
		return $this->app->input->is_post();
	}

}
