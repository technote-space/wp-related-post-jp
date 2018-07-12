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
 * Class Bigram
 * @package Related_Post\Tests
 */
class Bigram extends \Technote\Tests\Base {

	/** @var \Related_Post\Models\Analyzer\Bigram */
	private $bigram;

	public function _setup() {
		$this->bigram = \Related_Post\Models\Analyzer\Bigram::get_instance( $this->app );
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
		return array(
			array( '', array() ),
			array( ' 　 ', array( '  ', '  ' ) ),
			array( 'ab c d  efg', array( 'ab', 'c', 'd', '  ', 'efg' ) ),
			array( '　あ いう　　えお ', array( ' あ', 'あ ', ' い', 'いう', 'う ', '  ', ' え', 'えお', 'お ' ) ),
			array(
				'今日の天気は晴れです。 とてもHotです。',
				array(
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
					'す。'
				)
			),
			array(
				'こんにちは。Hello World!',
				array( 'こん', 'んに', 'にち', 'ちは', 'は。', 'Hello', 'World' )
			)
		);
	}
}
