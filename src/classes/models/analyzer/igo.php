<?php
/**
 * @version 1.1.3
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Igo
 * @package Related_Post\Classes\Models\Analyzer
 */
class Igo implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/** @var \Igo $igo */
	private $igo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->set_memory_limit();
		$library_dir = $this->app->define->plugin_dir . DS . 'library';
		/** @noinspection PhpIncludeInspection */
		require_once $library_dir . DS . 'igo-php-master' . DS . 'lib' . DS . 'Igo.php';
		$this->igo = new \Igo( $library_dir . DS . 'ipadic' );
	}

	/**
	 * set memory limit
	 */
	private function set_memory_limit() {
		ini_set( 'memory_limit', $this->apply_filters( 'igo_memory_limit', '256M' ) );
	}

	/**
	 * @param string $text
	 *
	 * @return array 解析結果の形態素リスト
	 */
	public function parse( $text ) {
		/** @var array $data */
		$data = $this->igo->parse( $text );

		return $data;
	}

	/**
	 * @param string $text
	 *
	 * @return array 分かち書きされた文字列のリスト
	 */
	public function wakati( $text ) {
		/** @var array $data */
		$data = $this->igo->wakati( $text );

		return $data;
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	public function words( $text, $classes = [] ) {
		return array_values( array_map( function ( $m ) {
			return $m->surface;
		}, empty( $classes ) ? $this->parse( $text ) : array_filter( $this->parse( $text ), function ( $m ) use ( $classes ) {
			$feature = explode( ',', $m->feature );
			$class   = reset( $feature );

			return in_array( $class, $classes );
		} ) ) );
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	public function count( $text, $classes = [] ) {
		$words = $this->words( $text, $classes );
		$ret   = [];
		foreach ( $words as $word ) {
			! isset( $ret[ $word ] ) and $ret[ $word ] = 0;
			$ret[ $word ] ++;
		}

		return $ret;
	}

}