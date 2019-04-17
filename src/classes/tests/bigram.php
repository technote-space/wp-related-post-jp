<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Tests;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Bigram
 * @package Related_Post\Classes\Tests
 */
class Bigram extends \WP_Framework_Test\Classes\Tests\Base {

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
