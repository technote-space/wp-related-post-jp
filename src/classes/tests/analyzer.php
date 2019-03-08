<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.2.0 Updated: test
 * @since 1.2.6 Added: remove comment test
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Tests;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Analyzer
 * @package Related_Post\Classes\Tests
 */
class Analyzer extends \WP_Framework_Test\Classes\Tests\Base {

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
		return [
			[ 'テスト', [ 'てすと' => 1 ] ],
			[ 'test <pre>Hello world! </pre> テスト', [ 'test' => 1, 'てすと' => 1 ] ],
			[ "test \n<pre class='php'> Hello world! </pre> \r\n テスト \n テスト", [ 'test' => 1, 'てすと' => 2 ] ],
			[ file_get_contents( __DIR__ . DS . 'test1.txt' ), [ 'こーど' => 1, '除去' => 1, 'てすと' => 1 ] ],
			[ 'test <!-- comment コメント　こめんと　　　 !"#$%&\'()=~|\^-[]:@`*}{\/_?:;.,><+ -->', [ 'test' => 1 ] ],
			[ file_get_contents( __DIR__ . DS . 'test2.txt' ), [ 'こめんと' => 1, '除去' => 3, 'てすと' => 4, 'test' => 3 ] ],
			[ '&nbsp;テスト&amp;テスト&apos;テスト', [ 'てすと' => 3 ] ],
			[ '<p>テスト</p><p><a href="https://example.com/test/?abc=123#xyz">https://example.com/test/?abc=123#xyz</a></p>', [ 'てすと' => 1 ] ],
		];
	}
}
