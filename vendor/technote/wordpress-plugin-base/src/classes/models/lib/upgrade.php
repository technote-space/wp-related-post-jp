<?php
/**
 * Technote Classes Models Lib Upgrade
 *
 * @version 2.4.3
 * @author technote-space
 * @since 2.4.0
 * @since 2.4.1 Added: show_plugin_update_notices method
 * @since 2.4.3 Fixed: get plugin upgrade notice from plugin directory
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Classes\Models\Lib;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Upgrade
 * @package Technote\Classes\Models\Lib
 */
class Upgrade implements \Technote\Interfaces\Loader {

	use \Technote\Traits\Loader;

	/**
	 * upgrade
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function upgrade() {
		if ( ! $this->is_required_upgrade() ) {
			return;
		}
		$last_version = $this->get_last_upgrade_version();
		$this->set_last_upgrade_version();

		$upgrades = [];
		foreach ( $this->get_class_list() as $class ) {
			/** @var \Technote\Interfaces\Upgrade $class */
			foreach ( $class->get_upgrade_methods() as $items ) {
				if ( ! is_array( $items ) ) {
					continue;
				}
				$version  = $this->app->utility->array_get( $items, 'version' );
				$callback = $this->app->utility->array_get( $items, 'callback' );
				if ( ! isset( $version ) || empty( $callback ) || ! is_string( $version ) ) {
					continue;
				}
				if ( $last_version && version_compare( $version, $last_version, '>=' ) ) {
					continue;
				}
				if ( ! is_callable( $callback ) && ( ! is_string( $callback ) || ! method_exists( $class, $callback ) || ! is_callable( [ $class, $callback ] ) ) ) {
					continue;
				}
				$upgrades[ $version ][] = is_callable( $callback ) ? $callback : [ $class, $callback ];
			}
		}
		if ( empty( $upgrades ) ) {
			return;
		}

		uksort( $upgrades, 'version_compare' );
		foreach ( $upgrades as $version => $items ) {
			foreach ( $items as $item ) {
				call_user_func( $item );
			}
		}
	}

	/**
	 * @return array
	 */
	protected function get_namespaces() {
		return [
			$this->app->define->plugin_namespace,
		];
	}

	/**
	 * @return string
	 */
	protected function get_instanceof() {
		return '\Technote\Interfaces\Upgrade';
	}

	/**
	 * @return string
	 */
	private function get_last_upgrade_version_option_key() {
		return 'last_upgrade_version';
	}

	/**
	 * @return mixed
	 */
	private function get_last_upgrade_version() {
		return $this->app->get_option( $this->get_last_upgrade_version_option_key() );
	}

	/**
	 * @return bool
	 */
	private function set_last_upgrade_version() {
		return $this->app->option->set( $this->get_last_upgrade_version_option_key(), $this->app->get_plugin_version() );
	}

	/**
	 * @return bool
	 */
	private function is_required_upgrade() {
		$version = $this->get_last_upgrade_version();

		return empty( $version ) || version_compare( $version, $this->app->get_plugin_version(), '<' );
	}

	/**
	 * show plugin upgrade notices
	 * @since 2.4.1
	 * @since 2.4.3 Fixed: get plugin upgrade notice from plugin directory
	 */
	public function show_plugin_update_notices() {
		add_action( 'in_plugin_update_message-' . $this->app->define->plugin_base_name, function ( $data, $r ) {
			$new_version = $r->new_version;
			$url         = $this->app->utility->array_get( $data, 'PluginURI' );
			$notices     = $this->get_upgrade_notices( $new_version, $url );
			if ( ! empty( $notices ) ) {
				$this->get_view( 'admin/include/upgrade', [
					'notices' => $notices,
				], true );
			}
		}, 10, 2 );
	}

	/**
	 * @since 2.4.3
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	private function get_plugin_readme( $slug ) {
		return $this->apply_filters( 'plugin_readme', 'https://plugins.svn.wordpress.org/' . $slug . '/trunk/readme.txt', $slug );
	}

	/**
	 * @since 2.4.3
	 *
	 * @param string $version
	 * @param string $url
	 *
	 * @return bool|mixed
	 */
	private function get_upgrade_notices( $version, $url ) {
		$slug = $this->get_plugin_slug( $url );
		if ( empty( $slug ) ) {
			return false;
		}

		$transient_name = 'upgrade_notice-' . $slug . '_' . $version;
		$upgrade_notice = false;//get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( $this->get_plugin_readme( $slug ) );
			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		return $upgrade_notice;
	}

	/**
	 * @since 2.4.3
	 *
	 * @param string $url
	 *
	 * @return false|string
	 */
	private function get_plugin_slug( $url ) {
		if ( $this->app->utility->starts_with( $url, 'https://wordpress.org/plugins/' ) ) {
			return trim( str_replace( 'https://wordpress.org/plugins/', '', $url ), '/' );
		}

		return false;
	}

	/**
	 * @since 2.4.3
	 *
	 * @param string $content
	 *
	 * @return array
	 */
	private function parse_update_notice( $content ) {
		$notices = [];
		if ( preg_match( '#==\s*Upgrade Notice\s*==([\s\S]+?)==#', $content, $matches ) ) {
			foreach ( (array) preg_split( '~[\r\n]+~', trim( $matches[1] ) ) as $line ) {
				$line      = preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				$line      = preg_replace( '#^\s*\*+\s*#', '', $line );
				$line      = preg_replace( '#^\s*=\s*([^\s]+)\s*=\s*$#', '[ $1 ]', $line );
				$notices[] = $line;
			}
		}

		return $notices;
	}
}
