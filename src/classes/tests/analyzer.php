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

namespace Related_Post\Classes\Tests;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Analyzer
 * @package Related_Post\Classes\Tests
 */
class Analyzer extends \Technote\Classes\Tests\Base {

	/** @var \Related_Post\Classes\Models\Analyzer */
	private $analyzer;

	public function _setup() {
		$this->analyzer = \Related_Post\Classes\Models\Analyzer::get_instance( $this->app );
	}

	public function test_parse() {
		$posts = get_posts( 'numberposts=1&orderby=rand' );
		if ( ! empty( $posts ) ) {
			$post = reset( $posts );
			$this->dump( $this->analyzer->parse( $post ) );
		}
	}
}
