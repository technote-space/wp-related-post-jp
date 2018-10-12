<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Tests;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Igo
 * @package Related_Post\Tests
 */
class Igo extends \Technote\Tests\Base {

	/** @var \Related_Post\Models\Analyzer\Igo */
	private $igo;

	public function _setup() {
		$this->igo = \Related_Post\Models\Analyzer\Igo::get_instance( $this->app );
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
		return array(
			array( 'すもももももももものうち', array( 'すもも', 'も', 'もも', 'も', 'もも', 'の', 'うち' ) ),
			array( 'これはテストです。', array( 'これ', 'は', 'テスト', 'です', '。' ) ),
			array( '今日の東京の天気は晴れです。', array( '今日', 'の', '東京', 'の', '天気', 'は', '晴れ', 'です', '。' ) ),
		);
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
		return array(
			array( 'すもももももももものうち', array(), array( 'すもも', 'も', 'もも', 'も', 'もも', 'の', 'うち' ) ),
			array( 'すもももももももものうち', array( '名詞', '??' ), array( 'すもも', 'もも', 'もも', 'うち' ) ),
			array( 'これはテストです。', array(), array( 'これ', 'は', 'テスト', 'です', '。' ) ),
			array( 'これはテストです。', array( '名詞', '??' ), array( 'これ', 'テスト' ) ),
			array( '今日の東京の天気は晴れです。', array(), array( '今日', 'の', '東京', 'の', '天気', 'は', '晴れ', 'です', '。' ) ),
			array( '今日の東京の天気は晴れです。', array( '名詞', '??' ), array( '今日', '東京', '天気', '晴れ' ) ),
			array( 'This is a pen.', array( '名詞', '??' ), array( 'This', 'is', 'a', 'pen', '.' ) ),
		);
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
		return array(
			array( 'すもももももももものうち', array(), array( 'すもも' => 1, 'も' => 2, 'もも' => 2, 'の' => 1, 'うち' => 1 ) ),
			array( 'すもももももももものうち', array( '名詞' ), array( 'すもも' => 1, 'もも' => 2, 'うち' => 1 ) ),
			array( 'これはテストです。', array(), array( 'これ' => 1, 'は' => 1, 'テスト' => 1, 'です' => 1, '。' => 1 ) ),
			array( 'これはテストです。', array( '名詞' ), array( 'これ' => 1, 'テスト' => 1 ) ),
			array(
				'今日の東京の天気は晴れです。',
				array(),
				array( '今日' => 1, 'の' => 2, '東京' => 1, '天気' => 1, 'は' => 1, '晴れ' => 1, 'です' => 1, '。' => 1 )
			),
			array(
				'今日の東京の天気は晴れです。',
				array( '名詞' ),
				array( '今日' => 1, '東京' => 1, '天気' => 1, '晴れ' => 1 )
			),
		);
	}
}
