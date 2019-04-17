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
 * Class Igo
 * @package Related_Post\Classes\Tests
 */
class Igo extends \WP_Framework_Test\Classes\Tests\Base {

	/** @var \Related_Post\Classes\Models\Analyzer\Igo */
	private $igo;

	public function _setup() {
		$this->igo = \Related_Post\Classes\Models\Analyzer\Igo::get_instance( $this->app );
	}

	/**
	 * @dataProvider _test_wakati_provider
	 *
	 * @param $text
	 * @param $expected
	 */
	public function test_wakati( $text, $expected ) {
		$this->assertEquals( $expected, $this->igo->wakati( $text ) );
	}

	/**
	 * @return array
	 */
	public function _test_wakati_provider() {
		return [
			[ 'すもももももももものうち', [ 'すもも', 'も', 'もも', 'も', 'もも', 'の', 'うち' ] ],
			[ 'これはテストです。', [ 'これ', 'は', 'テスト', 'です', '。' ] ],
			[ '今日の東京の天気は晴れです。', [ '今日', 'の', '東京', 'の', '天気', 'は', '晴れ', 'です', '。' ] ],
		];
	}

	/**
	 * @dataProvider _test_words_provider
	 *
	 * @param $text
	 * @param $classes
	 * @param $expected
	 */
	public function test_words( $text, $classes, $expected ) {
		$this->assertEquals( $expected, $this->igo->words( $text, $classes ) );
	}

	/**
	 * @return array
	 */
	public function _test_words_provider() {
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
	 * @dataProvider _test_count
	 *
	 * @param $text
	 * @param $classes
	 * @param $expected
	 */
	public function test_count( $text, $classes, $expected ) {
		$this->assertEquals( $expected, $this->igo->count( $text, $classes ) );
	}

	/**
	 * @return array
	 */
	public function _test_count() {
		return [
			[ 'すもももももももものうち', [], [ 'すもも' => 1, 'も' => 2, 'もも' => 2, 'の' => 1, 'うち' => 1 ] ],
			[ 'すもももももももものうち', [ '名詞' ], [ 'すもも' => 1, 'もも' => 2, 'うち' => 1 ] ],
			[ 'これはテストです。', [], [ 'これ' => 1, 'は' => 1, 'テスト' => 1, 'です' => 1, '。' => 1 ] ],
			[ 'これはテストです。', [ '名詞' ], [ 'これ' => 1, 'テスト' => 1 ] ],
			[
				'今日の東京の天気は晴れです。',
				[],
				[ '今日' => 1, 'の' => 2, '東京' => 1, '天気' => 1, 'は' => 1, '晴れ' => 1, 'です' => 1, '。' => 1 ],
			],
			[
				'今日の東京の天気は晴れです。',
				[ '名詞' ],
				[ '今日' => 1, '東京' => 1, '天気' => 1, '晴れ' => 1 ],
			],
		];
	}
}
