<?php
/**
 * Technote Models Session
 *
 * @version 1.1.29
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

	/** @var bool $_initialized */
	private $_initialized = false;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->check_session();
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
		return $this->apply_filters( 'user_check_session', 'user_check' );
	}

	/**
	 * check
	 */
	private function check_session() {
		if ( ! isset( $_SESSION ) ) {
			@session_start();
		}
		$this->_initialized = isset( $_SESSION );
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
		if ( $this->_initialized ) {
			session_regenerate_id( true );
		}
	}

	/**
	 * destroy
	 */
	public function destroy() {
		if ( $this->_initialized ) {
			$_SESSION = [];
			setcookie( session_name(), '', time() - 1, '/' );
			session_destroy();
			$this->_initialized = false;
		}
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $default = null ) {
		if ( ! $this->_initialized ) {
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
		if ( ! $this->_initialized ) {
			return;
		}
		$key              = $this->get_session_key( $key );
		$_SESSION[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete( $key ) {
		if ( ! $this->_initialized ) {
			return;
		}
		$key = $this->get_session_key( $key );
		if ( array_key_exists( $key, $_SESSION ) ) {
			unset( $_SESSION[ $key ] );
		}
	}
}
