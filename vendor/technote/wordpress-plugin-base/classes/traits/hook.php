<?php
/**
 * Technote Traits Hook
 *
 * @version 1.1.26
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Hook
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Hook {

	/** @var bool $_is_valid_cache */
	private $_is_valid_cache;

	/** @var array $_cache */
	private $_cache = [];

	/** @var array $_prevent_cache */
	private $_prevent_cache;

	/**
	 * load cache settings
	 */
	private function load_cache_settings() {
		if ( isset( $this->_is_valid_cache ) ) {
			return;
		}

		$this->_is_valid_cache = ! empty( $this->app->get_config( 'config', 'cache_filter_result' ) );
		$this->_prevent_cache  = $this->app->get_config( 'config', 'cache_filter_exclude_list', [] );
		$this->_prevent_cache  = empty( $this->_prevent_cache ) ? [] : array_combine( $this->_prevent_cache, array_fill( 0, count( $this->_prevent_cache ), true ) );
	}

	/**
	 * @return string
	 */
	protected function get_filter_prefix() {
		return $this->get_slug( 'filter_prefix', '' ) . '-';
	}

	/**
	 * @return mixed
	 */
	public function apply_filters() {
		$args = func_get_args();
		$key  = $args[0];

		$this->load_cache_settings();
		$is_valid_cache = $this->_is_valid_cache && ! isset( $this->_prevent_cache[ $key ] );
		if ( array_key_exists( $key, $this->_cache ) ) {
			return $this->_cache[ $key ];
		}

		$args[0] = $this->get_filter_prefix() . $key;
		if ( count( $args ) < 2 ) {
			$args[] = null;
		}
		$default = call_user_func_array( 'apply_filters', $args );

		if ( ! empty( $this->app->setting ) && $this->app->setting->is_setting( $key ) ) {
			$setting = $this->app->setting->get_setting( $key );
			$default = \Technote\Models\Utility::array_get( $setting, 'default', $default );
			$value   = $this->app->get_option( $args[0], null );
			if ( ! isset( $value ) || $value === '' ) {
				$value = $default;
			}

			$type = \Technote\Models\Utility::array_get( $setting, 'type', '' );
			if ( is_callable( [ $this, 'get_' . $type . '_value' ] ) ) {
				$value = call_user_func( [ $this, 'get_' . $type . '_value' ], $value, $default, $setting );
			}
			if ( ! empty( $setting['translate'] ) && $value === $default ) {
				$value = $this->app->translate( $value );
			}

			if ( $is_valid_cache ) {
				$this->_cache[ $key ] = $value;
			}

			return $value;
		}

		if ( $is_valid_cache && count( $args ) <= 2 ) {
			$this->_cache[ $key ] = $default;
		}

		return $default;
	}

	/**
	 * @param mixed $value
	 * @param mixed $default
	 * @param array $setting
	 *
	 * @return bool
	 */
	protected function get_bool_value(
		/** @noinspection PhpUnusedParameterInspection */
		$value, $default, $setting
	) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( 'true' === $value ) {
			return true;
		}
		if ( 'false' === $value ) {
			return false;
		}
		if ( isset( $value ) && (string) $value !== '' ) {
			return ! empty( $value );
		}

		return ! empty( $default );
	}

	/**
	 * @param mixed $value
	 * @param mixed $default
	 * @param array $setting
	 *
	 * @return int
	 */
	protected function get_int_value( $value, $default, $setting ) {
		$default = (int) $default;
		if ( is_numeric( $value ) ) {
			$value = (int) $value;
			if ( $value !== $default ) {
				if ( isset( $setting['min'] ) && $value < (int) $setting['min'] ) {
					$value = (int) $setting['min'];
				}
				if ( isset( $setting['max'] ) && $value > (int) $setting['max'] ) {
					$value = (int) $setting['max'];
				}
			} elseif ( isset( $setting['option'] ) ) {
				$default = isset( $setting['option_default'] ) ? (int) $setting['option_default'] : $default;
				$value   = (int) $this->app->get_option( $setting['option'], $default );
			}
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param mixed $default
	 * @param array $setting
	 *
	 * @return float
	 */
	protected function get_float_value( $value, $default, $setting ) {
		$default = (float) $default;
		if ( is_numeric( $value ) ) {
			$value = (float) $value;
			if ( $value !== $default ) {
				if ( isset( $setting['min'] ) && $value < (float) $setting['min'] ) {
					$value = (float) $setting['min'];
				}
				if ( isset( $setting['max'] ) && $value > (float) $setting['max'] ) {
					$value = (float) $setting['max'];
				}
			} elseif ( isset( $setting['option'] ) ) {
				$default = isset( $setting['option_default'] ) ? (float) $setting['option_default'] : $default;
				$value   = (float) $this->app->get_option( $setting['option'], $default );
			}
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * do action
	 */
	public function do_action() {
		$args    = func_get_args();
		$args[0] = $this->get_filter_prefix() . $args[0];
		call_user_func_array( 'do_action', $args );
	}

}
