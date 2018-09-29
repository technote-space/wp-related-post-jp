<?php
/**
 * Technote Traits Nonce
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
 * Trait Nonce
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Nonce {

	/**
	 * @return string
	 */
	abstract public function get_nonce_slug();

	/**
	 * @return string
	 */
	private function get_nonce_key() {
		$slug       = $this->get_slug( 'nonce_key', '_nonce' );
		$nonce_slug = $this->get_nonce_slug();

		return $this->apply_filters( 'get_nonce_key', $slug . '_' . $nonce_slug, $slug, $nonce_slug );
	}

	/**
	 * @return string
	 */
	private function get_nonce_action() {
		$slug       = $this->get_slug( 'nonce_action', '_nonce_action' );
		$nonce_slug = $this->get_nonce_slug();

		return $this->apply_filters( 'get_nonce_action', $slug . '_' . $nonce_slug, $slug, $nonce_slug );
	}

	/**
	 * @return string
	 */
	protected function create_nonce() {
		return wp_create_nonce( $this->get_nonce_action() );
	}

	/**
	 * @return bool
	 */
	private function nonce_check() {
		$nonce_key = $this->get_nonce_key();

		return $this->is_post() && isset( $_POST[ $nonce_key ] ) && wp_verify_nonce( $_POST[ $nonce_key ], $this->get_nonce_action() );
	}

}
