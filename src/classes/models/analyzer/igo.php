<?php
/**
 * @version 1.3.17
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

use Exception;
use Igo\Tagger;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Igo
 * @package Related_Post\Classes\Models\Analyzer
 */
class Igo implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

	/** @var Tagger $igo */
	private $igo;

	/**
	 * initialize
	 * @throws Exception
	 */
	protected function initialize() {
		$this->set_memory_limit();
		$this->igo = new Tagger( [ 'dict_dir' => $this->app->define->plugin_configs_dir . DS . 'ipadic' ] );
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
			$ret[ $word ]++;
		}

		return $ret;
	}

}
