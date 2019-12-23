<?php
/**
 * Class Igo Test
 *
 * @package Tests
 */

namespace Related_Post\Tests;

use PHPUnit\Framework\TestCase;
use Related_Post\Classes\Models\Analyzer\Igo;
use WP_Framework;
use WP_UnitTestCase;

/**
 * @noinspection PhpUndefinedClassInspection
 * Igo test case.
 *
 * @mixin TestCase
 */
class IgoTest extends WP_UnitTestCase {

	/**
	 * @var WP_Framework
	 */
	protected static $app;

	/** @var Igo */
	private static $igo;

	/**
	 * @SuppressWarnings(StaticAccess)
	 */
	public static function setUpBeforeClass() {
		static::$app = WP_Framework::get_instance( WP_RELATED_POST_JP );
		static::$igo = Igo::get_instance( static::$app );
	}

	/**
	 * @dataProvider data_provider_test_wakati
	 *
	 * @param $text
	 * @param $expected
	 */
	public function test_wakati( $text, $expected ) {
		$this->assertEquals( $expected, static::$igo->wakati( $text ) );
	}

	/**
	 * @return array
	 */
	public function data_provider_test_wakati() {
		return [
			[ 'すもももももももものうち', [ 'すもも', 'も', 'もも', 'も', 'もも', 'の', 'うち' ] ],
			[ 'これはテストです。', [ 'これ', 'は', 'テスト', 'です', '。' ] ],
			[ '今日の東京の天気は晴れです。', [ '今日', 'の', '東京', 'の', '天気', 'は', '晴れ', 'です', '。' ] ],
		];
	}

	/**
	 * @dataProvider data_provider_test_words
	 *
	 * @param $text
	 * @param $classes
	 * @param $expected
	 */
	public function test_words( $text, $classes, $expected ) {
		$this->assertEquals( $expected, static::$igo->words( $text, $classes ) );
	}

	/**
	 * @return array
	 */
	public function data_provider_test_words() {
		return [
			[ 'すもももももももものうち', [], [ 'すもも', 'も', 'もも', 'も', 'もも', 'の', 'うち' ] ],
			[ 'すもももももももものうち', [ '名詞', '??' ], [ 'すもも', 'もも', 'もも', 'うち' ] ],
			[ 'これはテストです。', [], [ 'これ', 'は', 'テスト', 'です', '。' ] ],
			[ 'これはテストです。', [ '名詞', '??' ], [ 'これ', 'テスト' ] ],
			[ '今日の東京の天気は晴れです。', [], [ '今日', 'の', '東京', 'の', '天気', 'は', '晴れ', 'です', '。' ] ],
			[ '今日の東京の天気は晴れです。', [ '名詞', '??' ], [ '今日', '東京', '天気', '晴れ' ] ],
			[ 'This is a pen.', [ '名詞', '??' ], [ 'This', 'is', 'a', 'pen', '.' ] ],
		];
	}

	/**
	 * @dataProvider data_provider_test_count
	 *
	 * @param $text
	 * @param $classes
	 * @param $expected
	 */
	public function test_count( $text, $classes, $expected ) {
		$this->assertEquals( $expected, static::$igo->count( $text, $classes ) );
	}

	/**
	 * @return array
	 */
	public function data_provider_test_count() {
		return [
			[
				'すもももももももものうち',
				[],
				[
					'すもも' => 1,
					'も'   => 2,
					'もも'  => 2,
					'の'   => 1,
					'うち'  => 1,
				],
			],
			[
				'すもももももももものうち',
				[ '名詞' ],
				[
					'すもも' => 1,
					'もも'  => 2,
					'うち'  => 1,
				],
			],
			[
				'これはテストです。',
				[],
				[
					'これ'  => 1,
					'は'   => 1,
					'テスト' => 1,
					'です'  => 1,
					'。'   => 1,
				],
			],
			[
				'これはテストです。',
				[ '名詞' ],
				[
					'これ'  => 1,
					'テスト' => 1,
				],
			],
			[
				'今日の東京の天気は晴れです。',
				[],
				[
					'今日' => 1,
					'の'  => 2,
					'東京' => 1,
					'天気' => 1,
					'は'  => 1,
					'晴れ' => 1,
					'です' => 1,
					'。'  => 1,
				],
			],
			[
				'今日の東京の天気は晴れです。',
				[ '名詞' ],
				[
					'今日' => 1,
					'東京' => 1,
					'天気' => 1,
					'晴れ' => 1,
				],
			],
		];
	}
}
