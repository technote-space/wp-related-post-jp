<?php
/**
 * Class Analyzer Test
 *
 * @package Tests
 */

namespace Related_Post\Tests;

use PHPUnit\Framework\TestCase;
use Related_Post\Classes\Models\Analyzer;
use WP_Framework;
use WP_Post;
use WP_UnitTestCase;

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

	private function get_post() {
		return new WP_Post( (object) [
			'post_author'           => 1,
			'post_content'          => '&nbsp;テスト&amp;テスト&apos;テスト&apos;123&apos;あ&apos;＄％＆&amp;`&amp;aaaaaaaaaaaaaaaaaaaaaaaaa',
			'post_content_filtered' => '',
			'post_title'            => '&nbsp;タイトル&amp;タイトル&apos;タイトル',
			'post_excerpt'          => '',
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'comment_status'        => '',
			'ping_status'           => '',
			'post_password'         => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_parent'           => 0,
			'menu_order'            => 0,
			'guid'                  => '',
			'import_id'             => 0,
			'context'               => '',
		] );
	}

	public function test_parse1() {
		$this->set_filter_value( 'extractor', 'title_content' );
		$this->assertEquals( [
			'たいとる'                     => 9,
			'てすと'                      => 3,
			'aaaaaaaaaaaaaaaaaaaaaaaa' => 1,
		], static::$analyzer->parse( $this->get_post() ) );
	}

	public function test_parse2() {
		$this->set_filter_value( 'extractor', 'title' );
		$this->assertEquals( [
			'たいとる' => 3,
		], static::$analyzer->parse( $this->get_post() ) );
	}

	public function test_parse3() {
		$this->set_filter_value( 'extractor', 'content' );
		$this->assertEquals( [
			'てすと'                      => 3,
			'aaaaaaaaaaaaaaaaaaaaaaaa' => 1,
		], static::$analyzer->parse( $this->get_post() ) );
	}

	public function test_parse4() {
		$this->set_filter_value( 'extractor', 'title_content_tags' );
		$this->assertEquals( [
			'たいとる'                     => 9,
			'てすと'                      => 3,
			'aaaaaaaaaaaaaaaaaaaaaaaa' => 1,
		], static::$analyzer->parse( $this->get_post() ) );
	}

	public function test_parse5() {
		$this->set_filter_value( 'extractor', 'title_content' );
		$this->set_filter_value( 'get_option', '1' );
		$this->assertEquals( [
			'タイ' => 9,
			'イト' => 9,
			'トル' => 9,
			'テス' => 3,
			'スト' => 3,
			'12' => 1,
			'23' => 1,
			'aa' => 24,
		], static::$analyzer->parse( $this->get_post() ) );
	}

	private function set_filter_value( $key, $value ) {
		$cache         = static::$app->get_shared_object( '_hook_cache' );
		$cache[ $key ] = $value;
		static::$app->set_shared_object( '_hook_cache', $cache );
	}
}
