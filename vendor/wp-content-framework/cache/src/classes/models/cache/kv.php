<?php
/**
 * WP_Framework_Cache Classes Models Cache Kv
 *
 * @version 0.0.3
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Cache\Classes\Models\Cache;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Kv
 * @package WP_Framework_Cache\Classes\Models\Cache
 */
class Kv implements \WP_Framework_Cache\Interfaces\Cache {

	use \WP_Framework_Cache\Traits\Cache;

	/**
	 * @var array $_cache
	 */
	private $_cache = [];

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return string
	 */
	private function get_key( $key, $group ) {
		return str_replace( '.', '_', $group ) . '.' . str_replace( '.', '_', $key );
	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return array
	 */
	private function get_option( $key, $group ) {
		$cache = $this->app->array->get( $this->_cache, $this->get_key( $key, $group ), null );
		if ( empty( $cache ) || ! is_array( $cache ) || count( $cache ) !== 2 ) {
			return [ false, null ];
		}
		list( $value, $time ) = $cache;

		return [ empty( $time ) || $time >= time(), $value ];
	}

	/**
	 * @param string $key
	 * @param string $group
	 * @param mixed $value
	 * @param null|int $expire
	 *
	 * @return bool
	 */
	private function set_option( $key, $group, $value, $expire ) {
		$expire = (int) $expire;
		$cache  = $this->app->array->get( $this->_cache, $this->get_key( $key, $group ), null );

		$expire       = $expire > 0 ? time() + $expire : null;
		$this->_cache = $this->app->array->set( $this->_cache, $this->get_key( $key, $group ), [
			$value,
			$expire,
		] );

		if ( empty( $cache ) || ! is_array( $cache ) || count( $cache ) !== 2 ) {
			return true;
		}

		list( $prev, $time ) = $cache;

		return $prev !== $value || $time !== $expire;
	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return bool
	 */
	private function delete_option( $key, $group ) {
		$this->_cache = $this->app->array->delete( $this->_cache, $this->get_key( $key, $group ) );

		return true;
	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return bool
	 */
	public function exists( $key, $group = 'default' ) {
		return $this->app->array->exists( $this->_cache, $this->get_key( $key, $group ) );
	}

	/**
	 * @param string $key
	 * @param string $group
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $group = 'default', $default = null ) {
		list( $is_valid, $value ) = $this->get_option( $key, $group );

		return $is_valid ? $value : $default;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * @param string $group
	 * @param null|int $expire
	 *
	 * @return bool
	 */
	public function set( $key, $value, $group = 'default', $expire = null ) {
		return $this->set_option( $key, $group, $value, $expire );
	}

	/**
	 * @param string $key
	 * @param string $group
	 *
	 * @return bool
	 */
	public function delete( $key, $group = 'default' ) {
		return $this->exists( $key, $group ) ? $this->delete_option( $key, $group ) : false;
	}

	/**
	 * @return bool
	 */
	public function flush() {
		$this->_cache = [];

		return true;
	}
}
