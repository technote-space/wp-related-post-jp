<?php
/**
 * Technote Models Loader Controller Admin
 *
 * @version 1.1.13
 * @author technote-space
 * @since 1.0.0
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
 * @property \Technote\Controllers\Admin\Base $page
 */
class Admin implements \Technote\Interfaces\Loader, \Technote\Interfaces\Nonce {

	use \Technote\Traits\Loader, \Technote\Traits\Nonce;

	/** @var array */
	private $admin_controllers = null;

	/** @var array $messages */
	private $messages = [];

	/** @var \Technote\Controllers\Admin\Base */
	public $page;

	/**
	 * @return string
	 */
	private function get_setting_slug() {
		return $this->apply_filters( 'get_setting_slug', 'setting' );
	}

	/**
	 * @return string
	 */
	private function get_menu_slug() {
		return $this->get_page_prefix() . $this->apply_filters( 'get_menu_slug', $this->get_setting_slug() );
	}

	/**
	 * @return string
	 */
	private function get_plugin_title() {
		$plugin_title = $this->app->get_config( 'config', 'plugin_title' );
		empty( $plugin_title ) and $plugin_title = $this->app->original_plugin_name;

		return $this->apply_filters( 'get_plugin_title', $plugin_title );
	}

	/**
	 * @return \Technote\Controllers\Admin\Base|null
	 */
	private function load_page() {
		try {
			$prefix  = $this->get_page_prefix();
			$pattern = "#^{$prefix}(.+)#";
			if ( isset( $_GET['page'] ) && preg_match( $pattern, $_GET['page'], $matches ) ) {
				$page          = $matches[1];
				$exploded      = explode( '-', $page );
				$page          = array_pop( $exploded );
				$add_namespace = implode( '\\', array_map( 'ucfirst', $exploded ) );
				! empty( $add_namespace ) and $add_namespace .= '\\';
				$instance = $this->get_class_instance( $this->get_class_setting( $page, $add_namespace ), '\Technote\Controllers\Admin\Base' );
				if ( false !== $instance ) {
					/** @var \Technote\Controllers\Admin\Base $instance */
					$this->do_action( 'pre_load_admin_page', $instance );

					return $instance;
				}
				$page = isset( $_GET['page'] ) ? $_GET['page'] : 'Page';
				$this->app->log( sprintf( '%s not found.', $page ) );
			}
		} catch ( \Exception $e ) {
			$this->app->log( $e->getMessage() );
		}

		return null;
	}

	/**
	 * add menu
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_menu() {
		$capability = $this->app->get_config( 'capability', 'admin_menu', 'manage_options' );
		if ( ! $this->app->user_can( $capability ) ) {
			return;
		}

		$this->page = $this->load_page();
		if ( isset( $this->page ) && $this->app->user_can( $this->apply_filters( 'admin_menu_capability', $this->page->get_capability(), $this->page ) ) ) {
			$this->page->action();
			$this->do_action( 'post_load_admin_page', $this->page );
		}

		$pages = [];
		foreach ( $this->get_admin_controllers() as $page ) {
			/** @var \Technote\Controllers\Admin\Base $page */
			if ( $this->app->user_can( $this->apply_filters( 'admin_menu_capability', $page->get_capability(), $page ) ) ) {
				$priority = $this->apply_filters( 'admin_menu_priority', $page->get_priority(), $page );
				if ( $priority < 0 ) {
					continue;
				}
				if ( $this->get_menu_slug() === $this->get_page_prefix() . $page->get_page_slug() ) {
					$priority = - 1;
				}
				$pages[ $priority ][] = $page;
			}
		}
		ksort( $pages );

		$hook = add_menu_page(
			$this->get_plugin_title(),
			$this->get_plugin_title(),
			$capability,
			$this->get_menu_slug(),
			function () {
			},
			$this->get_img_url( $this->app->get_config( 'config', 'menu_image' ), '' ),
			$this->apply_filters( 'admin_menu_position' )
		);

		if ( isset( $this->page ) && $this->app->user_can( $this->page->get_capability() ) ) {
			add_action( "load-$hook", function () {
				$this->page->setup_help();
			} );
		}

		add_filter( 'plugin_action_links_' . $this->app->define->plugin_base_name, function ( $links ) {
			return $this->plugin_action_links( $links );
		} );

		/** @var \Technote\Controllers\Admin\Base $page */
		foreach ( \Technote\Models\Utility::flatten( $pages ) as $page ) {
			$hook = add_submenu_page(
				$this->get_menu_slug(),
				$this->app->translate( $page->get_page_title() ),
				$this->app->translate( $page->get_menu_name() ),
				$capability,
				$this->get_page_prefix() . $page->get_page_slug(),
				function () {
					$this->load();
				}
			);
			if ( $this->page ) {
				add_action( "load-$hook", function () {
					$this->page->setup_help();
				} );
			}
		}
	}

	/**
	 * @param $links
	 *
	 * @return array
	 */
	private function plugin_action_links( $links ) {
		$link = $this->get_view( 'admin/include/action_links', [
			'url' => menu_page_url( $this->get_menu_slug(), false ),
		] );
		array_unshift( $links, $link );

		return $links;
	}

	/**
	 * @return string
	 */
	public function get_nonce_slug() {
		return '_admin_main';
	}

	/**
	 * @return string
	 */
	private function get_page_prefix() {
		return $this->apply_filters( 'get_page_prefix', $this->get_slug( 'page_prefix', '' ) ) . '-';
	}

	/**
	 * @return array
	 */
	private function get_admin_controllers() {
		if ( ! isset( $this->admin_controllers ) ) {
			$this->admin_controllers = [];
			/** @var \Technote\Controllers\Admin\Base $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'controllers' . DS . 'admin', '\Technote\Controllers\Admin\Base' ) as $class ) {
				$slug = $class->get_page_slug();
				if ( ! isset( $this->admin_controllers[ $slug ] ) ) {
					$this->admin_controllers[ $slug ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'controllers' . DS . 'admin', '\Technote\Controllers\Admin\Base' ) as $class ) {
				$slug = $class->get_page_slug();
				if ( ! isset( $this->admin_controllers[ $slug ] ) ) {
					$this->admin_controllers[ $slug ] = $class;
				}
			}
		}

		return $this->admin_controllers;
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
			$this->app->define->plugin_namespace . '\\Controllers\\Admin\\',
			$this->app->define->lib_namespace . '\\Controllers\\Admin\\',
		];
	}

	/**
	 * load
	 */
	private function load() {
		if ( isset( $this->page ) ) {
			if ( $this->app->user_can( $this->page->get_capability() ) ) {
				$this->get_view( 'admin/include/layout', [
					'page' => $this->page,
					'slug' => $this->page->get_page_slug(),
				], true );
			} else {
				$this->get_view( 'admin/include/error', [ 'message' => 'Forbidden.' ], true );
			}
		} else {
			$this->get_view( 'admin/include/error', [ 'message' => 'Page not found.' ], true );
		}
	}


	/**
	 * admin notice
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function admin_notice() {
		if ( $this->app->user_can() ) {
			$this->get_view( 'admin/include/notice', [
				'messages' => $this->messages,
			], true );
		}
	}

	/**
	 * @param string $message
	 * @param string $group
	 * @param bool $escape
	 * @param bool $error
	 */
	public function add_message( $message, $group = '', $error = false, $escape = true ) {
		$this->messages[ $group ][ $error ? 'error' : 'updated' ][] = [ $message, $escape ];
	}

}
