<?php
/**
 * Technote Models Loader Controller Api
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models\Loader\Controller;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Admin
 * @package Technote\Models\Loader\Controller
 */
class Api implements \Technote\Interfaces\Loader {

	use \Technote\Traits\Loader;

	/** @var array */
	private $api_controllers = null;

	/**
	 * register script
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function register_script() {
		$functions = [];
		$scripts   = [];
		/** @var \Technote\Traits\Controller\Api $api */
		foreach ( $this->get_api_controllers( true ) as $api ) {
			$name               = $api->get_call_function_name();
			$functions[ $name ] = [
				'method'   => $api->get_method(),
				'endpoint' => $api->get_endpoint(),
			];
			$script             = is_admin() ? $api->admin_script() : $api->front_script();
			if ( ! empty( $script ) ) {
				$scripts[] = $script;
			}
		}
		if ( ! empty( $functions ) ) {
			$this->add_script_view( 'include/script/api', [
				'endpoint'  => rest_url(),
				'namespace' => $this->get_api_namespace(),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'functions' => $functions,
			], 9 );
			foreach ( $scripts as $script ) {
				$this->add_script( $script );
			}
		}
	}

	/**
	 * @return string
	 */
	private function get_api_namespace() {
		return $this->get_slug( 'api_namespace', '' ) . '/' . $this->app->get_config( 'config', 'api_version' );
	}

	/**
	 * register api
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function register_api() {
		foreach ( $this->get_api_controllers( false ) as $api ) {
			/** @var \Technote\Controllers\Api\Base $api */
			register_rest_route( $this->get_api_namespace(), $api->get_endpoint(), [
				'methods'             => strtoupper( $api->get_method() ),
				'permission_callback' => function () use ( $api ) {
					return $this->app->user_can( $api->get_capability() );
				},
				'args'                => $api->get_args_setting(),
				'callback'            => [ $api, 'callback' ],
			] );
		}
	}

	/**
	 * @param string $page
	 * @param string $add_namespace
	 *
	 * @return array
	 */
	protected function get_namespaces(
		/** @noinspection PhpUnusedParameterInspection */
		$page, $add_namespace
	) {
		return [
			$this->app->define->plugin_namespace . '\\Controllers\\Api\\',
			$this->app->define->lib_namespace . '\\Controllers\\Api\\',
		];
	}

	/**
	 * @param bool $filter
	 *
	 * @return array
	 */
	private function get_api_controllers( $filter ) {
		if ( ! isset( $this->api_controllers ) ) {
			$this->api_controllers = [];
			/** @var \Technote\Traits\Controller\Api $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'controllers' . DS . 'api', '\Technote\Controllers\Api\Base' ) as $class ) {
				$name = $class->get_call_function_name();
				if ( ! isset( $this->api_controllers[ $name ] ) ) {
					$this->api_controllers[ $name ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'controllers' . DS . 'api', '\Technote\Controllers\Api\Base' ) as $class ) {
				$name = $class->get_call_function_name();
				if ( ! isset( $this->api_controllers[ $name ] ) ) {
					$this->api_controllers[ $name ] = $class;
				}
			}
		}

		if ( $filter ) {
			foreach ( $this->api_controllers as $name => $class ) {
				if ( ! $class->is_valid() ) {
					unset( $this->api_controllers[ $name ] );
				}
			}
		}

		return $this->api_controllers;
	}


}
