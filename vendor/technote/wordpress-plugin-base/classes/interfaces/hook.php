<?php
/**
 * Technote Interfaces Hook
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Interfaces;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Interface Hook
 * @package Technote\Interfaces
 */
interface Hook {

	/**
	 * @return mixed
	 */
	public function apply_filters();

	/**
	 * do action
	 */
	public function do_action();

}
