<?php
/**
 * Technote Models Loader Cron
 *
 * @version 1.1.13
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models\Loader;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Cron
 * @package Technote\Models\Loader
 */
class Cron implements \Technote\Interfaces\Loader {

	use \Technote\Traits\Loader;

	/** @var array */
	protected $crons = null;

	/**
	 * initialized
	 */
	protected function initialized() {
		$this->get_crons();
	}

	/**
	 * @return array
	 */
	private function get_crons() {
		if ( ! isset( $this->crons ) ) {
			$this->crons = [];
			/** @var \Technote\Crons\Base $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'crons', '\Technote\Crons\Base' ) as $class ) {
				$slug = $class->class_name;
				if ( ! isset( $this->crons[ $slug ] ) ) {
					$this->crons[ $slug ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'crons', '\Technote\Crons\Base' ) as $class ) {
				$slug = $class->class_name;
				if ( ! isset( $this->crons[ $slug ] ) ) {
					$this->crons[ $slug ] = $class;
				}
			}
		}

		return $this->crons;
	}

	/**
	 * @return array
	 */
	public function get_cron_class_names() {
		return \Technote\Models\Utility::array_pluck( $this->get_crons(), 'class_name' );
	}

	/**
	 * @param $page
	 * @param $add_namespace
	 *
	 * @return array
	 */
	protected function get_namespaces(
		/** @noinspection PhpUnusedParameterInspection */
		$page, $add_namespace
	) {
		return [
			$this->app->define->plugin_namespace . '\\Crons',
			$this->app->define->lib_namespace . '\\Crons',
		];
	}

}
