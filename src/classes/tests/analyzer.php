<?php
/**
 * @version 1.2.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.2.0 Updated: test
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

	/**
	 * @dataProvider _test_parse_provider
	 *
	 * @param string $text
	 * @param array $expected
	 */
	public function test_text( $text, $expected ) {
		$this->assertEquals( $expected, $this->analyzer->parse_text( $text ) );
	}

	/**
	 * @return array
	 */
	public function _test_parse_provider() {
		$test = file_get_contents( __DIR__ . DS . 'test.txt' );

		return [
			[ 'テスト', [ 'てすと' => 1 ] ],
			[ 'test <pre>Hello world! </pre> テスト', [ 'test' => 1, 'てすと' => 1 ] ],
			[ "test \n<pre class='php'> Hello world! </pre> \r\n テスト \n テスト", [ 'test' => 1, 'てすと' => 2 ] ],
			[ $test, [ 'こーど' => 1, '除去' => 1, 'てすと' => 1 ] ],
		];
	}
}