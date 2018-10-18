<?php
/**
 * Technote Models User
 *
 * @version 1.1.13
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class User
 * @package Technote\Models
 * @property int $user_id
 * @property \WP_User $user_data
 * @property int $user_level
 * @property bool $super_admin
 * @property string $user_name
 * @property string $display_name
 * @property string $user_email
 * @property bool $logged_in
 */
class User implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Uninstall;

	/** @var int $user_id */
	public $user_id;
	/** @var \WP_User $user_data */
	public $user_data;
	/** @var int $user_level */
	public $user_level;
	/** @var bool $super_admin */
	public $super_admin;
	/** @var string $user_name */
	public $user_name;
	/** @var string $display_name */
	public $display_name;
	/** @var string $user_email */
	public $user_email;
	/** @var bool $logged_in */
	public $logged_in;

	/**
	 * initialize
	 */
	protected function initialize() {
		global $user_ID;
		$current_user = wp_get_current_user();

		if ( $user_ID ) {
			$this->user_data   = get_userdata( $user_ID );
			$this->user_level  = $this->user_data->user_level;
			$this->super_admin = is_super_admin( $user_ID );
		} else {
			$this->user_data   = $current_user;
			$this->user_level  = 0;
			$this->super_admin = false;
		}

		$this->user_id      = $this->user_data->ID;
		$this->user_name    = $this->user_data->user_login;
		$this->display_name = $this->user_data->display_name;
		$this->user_email   = $this->user_data->user_email;
		$this->logged_in    = is_user_logged_in();
		if ( empty( $this->user_name ) ) {
			$this->user_name = $this->app->input->ip();
		}
	}

	/**
	 * @return string
	 */
	private function get_user_prefix() {
		return $this->get_slug( 'user_prefix', '_user' ) . '-';
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_meta_key( $key ) {
		return $this->get_user_prefix() . $key;
	}

	/**
	 * @param string $key
	 * @param int|null $user_id
	 * @param bool $single
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $user_id = null, $single = true, $default = '' ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return $this->apply_filters( 'get_user_meta', $default, $key, $user_id, $single, $default );
		}

		return $this->apply_filters( 'get_user_meta', get_user_meta( $user_id, $this->get_meta_key( $key ), $single ), $key, $user_id, $single, $default, $this->get_user_prefix() );
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int|null $user_id
	 *
	 * @return bool|int
	 */
	public function set( $key, $value, $user_id = null ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return false;
		}

		return update_user_meta( $user_id, $this->get_meta_key( $key ), $value );
	}

	/**
	 * @param string $key
	 * @param int|null $user_id
	 * @param mixed $meta_value
	 *
	 * @return bool
	 */
	public function delete( $key, $user_id = null, $meta_value = '' ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return false;
		}

		return delete_user_meta( $user_id, $this->get_meta_key( $key ), $meta_value );
	}

	/**
	 * @param null|string|false $capability
	 *
	 * @return bool
	 */
	public function user_can( $capability = null ) {
		if ( ! isset( $capability ) ) {
			$capability = $this->app->get_config( 'capability', 'default_user', 'manage_options' );
		}
		if ( false === $capability ) {
			return true;
		}

		return current_user_can( $this->apply_filters( 'user_can', $capability ) );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s%%", $this->get_user_prefix() );
		$wpdb->query( $query );
	}
}
