<?php
/**
 * Technote Traits Loader
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
 * Trait Loader
 * @package Technote\Traits\Controller
 * @property \Technote $app
 */
trait Loader {

	use Singleton, Hook, Presenter;

	/** @var array */
	private $cache = array();

	/**
	 * @return string
	 */
	public function get_loader_name() {
		return $this->get_file_slug();
	}

	/**
	 * @param string $dir
	 * @param string $add_namespace
	 *
	 * @return \Generator
	 */
	protected function get_class_settings( $dir, $add_namespace = '' ) {
		$dir = rtrim( $dir, DS );
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( $file == '.' || $file == '..' ) {
					continue;
				}
				if ( is_file( $dir . DS . $file ) && preg_match( "/^[^\\.].*\\.php$/", $file ) ) {
					yield $this->get_class_setting( $this->app->get_page_slug( $file ), $add_namespace );
				} elseif ( is_dir( $dir . DS . $file ) ) {
					foreach ( $this->get_class_settings( $dir . DS . $file, $add_namespace . ucfirst( $file ) . '\\' ) as $class_setting ) {
						yield $class_setting;
					}
				}
			}
		}
	}

	/**
	 * @param string $dir
	 * @param string $instanceof
	 * @param bool $return_instance
	 *
	 * @return \Generator
	 */
	protected function get_classes( $dir, $instanceof, $return_instance = true ) {
		foreach ( $this->get_class_settings( $dir ) as $class_setting ) {
			$instance = $this->get_class_instance( $class_setting, $instanceof );
			if ( false !== $instance ) {
				if ( $return_instance ) {
					yield $instance;
				} else {
					yield $class_setting;
				}
			}
		}
	}

	/**
	 * @param string $page
	 *
	 * @return string
	 */
	private function get_class_name( $page ) {
		return $this->apply_filters( 'get_class_name', ucfirst( str_replace( DS, '\\', $page ) ), $page );
	}

	/**
	 * @param string $page
	 * @param string $add_namespace
	 *
	 * @return false|array
	 */
	protected function get_class_setting( $page, $add_namespace = '' ) {
		if ( 'base' === $page ) {
			return false;
		}
		if ( isset( $this->cache[ $add_namespace . $page ] ) ) {
			return $this->cache[ $add_namespace . $page ];
		}
		$namespaces = $this->get_namespaces( $page, $add_namespace );
		if ( ! empty( $namespaces ) ) {
			foreach ( $namespaces as $namespace ) {
				$class = rtrim( $namespace, '\\' ) . '\\' . $add_namespace . $this->get_class_name( $page );
				if ( class_exists( $class ) ) {
					$this->cache[ $add_namespace . $page ] = array( $class, $add_namespace );

					return $this->cache[ $add_namespace . $page ];
				}
			}
		}

		return false;
	}

	/**
	 * @param array $class_setting
	 * @param string $instanceof
	 *
	 * @return bool|Singleton
	 */
	protected function get_class_instance( $class_setting, $instanceof ) {
		if ( false !== $class_setting && class_exists( $class_setting[0] ) && is_subclass_of( $class_setting[0], '\Technote\Interfaces\Singleton' ) ) {
			try {
				/** @var Singleton[] $class_setting */
				$instance = $class_setting[0]::get_instance( $this->app );
				if ( $instance instanceof $instanceof ) {
					if ( $instance instanceof \Technote\Interfaces\Controller\Admin ) {
						$instance->set_relative_namespace( $class_setting[1] );
					}

					return $instance;
				}
			} catch ( \Exception $e ) {
			}
		}

		return false;
	}

	/**
	 * @param string $page
	 * @param string $add_namespace
	 *
	 * @return array
	 */
	protected abstract function get_namespaces( $page, $add_namespace );

}
