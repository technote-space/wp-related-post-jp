<?php
/**
 * Class Bigram Test
 *
 * @package Tests
 */

use PHPUnit\Framework\TestCase;

use Related_Post\Classes\Models\Analyzer\Bigram;

/**
 * @noinspection PhpUndefinedClassInspection
 * Bigram test case.
 *
 * @mixin TestCase
 */
class BigramTest extends WP_UnitTestCase {

	/**
	 * @var WP_Framework
	 */
	protected static $app;

	/** @var Bigram */
	private static $bigram;

	/**
	 * @SuppressWarnings(StaticAccess)
	 */
	public static function setUpBeforeClass() {
		static::$app    = WP_Framework::get_instance( WP_RELATED_POST_JP );
		static::$bigram = Bigram::get_instance( static::$app );
	}

	/**
	 * @dataProvider data_provider_test_words
	 *
	 * @param string $text
	 * @param array $expected
	 */
	public function test_words( $text, $expected ) {
		$this->assertEquals( $expected, static::$bigram->words( $text ) );
	}

	/**
	 * @return array
	 */
	public function data_provider_test_words() {
		return [
			[ '', [] ],
			[ ' 　 ', [ '  ', '  ' ] ],
			[ 'ab c d  efg', [ 'ab', 'c', 'd', '  ', 'efg' ] ],
			[ '　あ いう　　えお ', [ ' あ', 'あ ', ' い', 'いう', 'う ', '  ', ' え', 'えお', 'お ' ] ],
			[
				'今日の天気は晴れです。 とてもHotです。',
				[
					'今日',
					'日の',
					'の天',
					'天気',
					'気は',
					'は晴',
					'晴れ',
					'れで',
					'です',
					'す。',
					'。 ',
					' と',
					'とて',
					'ても',
					'Hot',
					'です',
					'す。',
				],
			],
			[
				'こんにちは。Hello World!',
				[ 'こん', 'んに', 'にち', 'ちは', 'は。', 'Hello', 'World' ],
			],
		];
	}
}
