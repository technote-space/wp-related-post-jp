<?php
/**
 * Class Analyzer Test
 *
 * @package Tests
 */

use PHPUnit\Framework\TestCase;

use Related_Post\Classes\Models\Analyzer;

/**
 * @noinspection PhpUndefinedClassInspection
 * Analyzer test case.
 *
 * @mixin TestCase
 */
class AnalyzerTest extends WP_UnitTestCase {

	/**
	 * @var WP_Framework
	 */
	protected static $app;

	/** @var Analyzer */
	private static $analyzer;

	/**
	 * @SuppressWarnings(StaticAccess)
	 */
	public static function setUpBeforeClass() {
		static::$app      = WP_Framework::get_instance( WP_RELATED_POST_JP );
		static::$analyzer = Analyzer::get_instance( static::$app );
	}

	/**
	 * @dataProvider data_provider_test_parse
	 *
	 * @param string|callable $text
	 * @param array $expected
	 */
	public function test_parse_text( $text, $expected ) {
		if ( is_callable( $text ) ) {
			$text = $text();
		}
		$this->assertEquals( $expected, static::$analyzer->parse_text( $text ) );
	}

	/**
	 * @return array
	 */
	public function data_provider_test_parse() {
		return [
			[ 'テスト', [ 'てすと' => 1 ] ],
			[
				'test <pre>Hello world! </pre> テスト',
				[
					'test' => 1,
					'てすと'  => 1,
				],
			],
			[
				"test \n<pre class='php'> Hello world! </pre> \r\n テスト \n テスト",
				[
					'test' => 1,
					'てすと'  => 2,
				],
			],
			[
				function () {
					return static::$app->file->get_contents( __DIR__ . DS . 'test1.txt' );
				},
				[
					'こーど' => 1,
					'除去'  => 1,
					'てすと' => 1,
				],
			],
			[
				'test <!-- comment コメント　こめんと　　　 !"#$%&\'()=~|\^-[]:@`*}{\/_?:;.,><+ -->',
				[
					'test' => 1,
				],
			],
			[
				function () {
					return static::$app->file->get_contents( __DIR__ . DS . 'test2.txt' );
				},
				[
					'こめんと' => 1,
					'除去'   => 3,
					'てすと'  => 4,
					'test' => 3,
				],
			],
			[ '&nbsp;テスト&amp;テスト&apos;テスト', [ 'てすと' => 3 ] ],
			[
				'<p>テスト</p><p><a href="https://example.com/test/?abc=123#xyz">https://example.com/test/?abc=123#xyz</a></p>',
				[
					'てすと' => 1,
				],
			],
		];
	}
}
