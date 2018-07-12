<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Tests;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Analyzer
 * @package Related_Post\Tests
 */
class Analyzer extends \Technote\Tests\Base {

	/** @var \Related_Post\Models\Analyzer */
	private $analyzer;

	public function _setup() {
		$this->analyzer = \Related_Post\Models\Analyzer::get_instance( $this->app );
	}

	public function test_parse() {
		$posts = get_posts( 'numberposts=1&orderby=rand' );
		if ( ! empty( $posts ) ) {
			$post = reset( $posts );
			$this->dump( $this->analyzer->parse( $post ) );
		}
	}
}
