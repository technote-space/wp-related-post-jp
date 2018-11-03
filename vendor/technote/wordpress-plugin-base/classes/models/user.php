<?php
/**
 * Technote Models User
 *
 * @version 1.1.38
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
 * @property string|false $user_role
 * @property array $user_roles
 * @property array $user_caps
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
	/** @var string|false $user_role */
	public $user_role;
	/** @var array $user_roles */
	public $user_roles;
	/** @var array $user_caps */
	public $user_caps;

	/**
	 * initialize
	 */
	protected function initialize() {
		$cache = $this->app->get_shared_object( 'user_info_cache', 'all' );
		if ( ! isset( $cache ) ) {
			global $user_ID;
			$current_user = wp_get_current_user();

			$cache = [];
			if ( $user_ID ) {
				$cache['user_data']   = get_userdata( $user_ID );
				$cache['user_level']  = $cache['user_data']->user_level;
				$cache['super_admin'] = is_super_admin( $user_ID );
			} else {
				$cache['user_data']   = $current_user;
				$cache['user_level']  = 0;
				$cache['super_admin'] = false;
			}
			$cache['user_id']      = $cache['user_data']->ID;
			$cache['user_name']    = $cache['user_data']->user_login;
			$cache['display_name'] = $cache['user_data']->display_name;
			$cache['user_email']   = $cache['user_data']->user_email;
			$cache['logged_in']    = is_user_logged_in();
			if ( empty( $cache['user_name'] ) ) {
				$cache['user_name'] = $this->app->input->ip();
			}
			if ( $cache['logged_in'] && ! empty( $cache['user_data']->roles ) ) {
				$roles               = array_values( $cache['user_data']->roles );
				$cache['user_roles'] = $roles;
				$cache['user_role']  = $roles[0];
			} else {
				$cache['user_roles'] = [];
				$cache['user_role']  = false;
			}
			$cache['user_caps'] = [];
			foreach ( $cache['user_roles'] as $r ) {
				$role = get_role( $r );
				if ( $role ) {
					$cache['user_caps'] = array_merge( $cache['user_caps'], $role->capabilities );
				}
			}
			$this->app->set_shared_object( 'user_info_cache', $cache, 'all' );
		}
		foreach ( $cache as $k => $v ) {
			$this->$k = $v;
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

		return $this->has_cap( $capability );
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function has_cap( $capability ) {
		return ! empty( $this->user_caps[ $capability ] );
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
