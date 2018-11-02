<?php
/**
 * Technote Models Session
 *
 * @version 1.1.37
 * @author technote-space
 * @since 1.1.25
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Session
 * @package Technote\Models
 */
class Session implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/**
	 * @return bool
	 */
	private function is_valid_session() {
		return $this->app->isset_shared_object( 'is_valid_session', 'all' );
	}

	/**
	 * initialize
	 */
	protected function initialize() {
		if ( ! $this->app->isset_shared_object( 'session_initialized', 'all' ) ) {
			$this->app->set_shared_object( 'session_initialized', true, 'all' );
			if ( ! $this->app->isset_shared_object( 'session_user_check_name', 'all' ) ) {
				$this->app->set_shared_object( 'session_user_check_name', 'user_check', 'all' );
			}
			$this->check_session();
		}
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_session_key( $key ) {
		return $this->apply_filters( 'session_key', $this->get_slug( 'session', '-session' ) ) . '-' . $key;
	}

	/**
	 * @return string
	 */
	private function get_user_check_name() {
		return $this->app->get_shared_object( 'session_user_check_name', 'all' );
	}

	/**
	 * check
	 */
	private function check_session() {
		if ( ! isset( $_SESSION ) ) {
			@session_start();
		}
		if ( isset( $_SESSION ) ) {
			$this->app->set_shared_object( 'is_valid_session', true, 'all' );
		}
		$this->security_process();
	}

	/**
	 * security
	 */
	private function security_process() {
		$check = $this->get( $this->get_user_check_name() );
		if ( ! isset( $check ) ) {
			$this->set( $this->get_user_check_name(), $this->app->user->user_id );
		} else {
			if ( $check != $this->app->user->user_id ) {
				// prevent session fixation
				$this->regenerate();
				$this->set( $this->get_user_check_name(), $this->app->user->user_id );
			}
		}
	}

	/**
	 * regenerate
	 */
	public function regenerate() {
		if ( $this->is_valid_session() ) {
			if ( ! $this->app->isset_shared_object( 'session_regenerated', 'all' ) ) {
				session_regenerate_id( true );
				$this->app->set_shared_object( 'session_regenerated', true, 'all' );
			}
		}
	}

	/**
	 * destroy
	 */
	public function destroy() {
		if ( $this->is_valid_session() ) {
			$_SESSION = [];
			setcookie( session_name(), '', time() - 1, '/' );
			session_destroy();
			$this->app->delete_shared_object( 'is_valid_session', 'all' );
		}
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( ! $this->is_valid_session() ) {
			return $default;
		}
		$key = $this->get_session_key( $key );
		if ( array_key_exists( $key, $_SESSION ) ) {
			return $_SESSION[ $key ];
		}

		return $default;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		if ( ! $this->is_valid_session() ) {
			return;
		}
		$key              = $this->get_session_key( $key );
		$_SESSION[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete( $key ) {
		if ( ! $this->is_valid_session() ) {
			return;
		}
		$key = $this->get_session_key( $key );
		if ( array_key_exists( $key, $_SESSION ) ) {
			unset( $_SESSION[ $key ] );
		}
	}
}
