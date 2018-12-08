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
 * Class Bigram
 * @package Related_Post\Classes\Tests
 */
class Bigram extends \Technote\Classes\Tests\Base {

	/** @var \Related_Post\Classes\Models\Analyzer\Bigram */
	private $bigram;

	public function _setup() {
		$this->bigram = \Related_Post\Classes\Models\Analyzer\Bigram::get_instance( $this->app );
	}

	/**
	 * @dataProvider _test_words_provider
	 *
	 * @param string $text
	 * @param array $expected
	 */
	public function test_words( $text, $expected ) {
		$this->assertEquals( $expected, $this->bigram->words( $text ) );
	}

	/**
	 * @return array
	 */
	public function _test_words_provider() {
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
