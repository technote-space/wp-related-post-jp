<?php
/**
 * Technote Models Utility Test
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Tests\Models;

/**
 * Class UtilityTest
 * @package Technote\Tests\Models
 * @group technote
 * @group models
 */
class UtilityTest extends \Technote\Tests\TestCase {

	/**
	 * @dataProvider _test_flatten_provider
	 *
	 * @param array $array
	 * @param bool $preserve_keys
	 * @param array $expected
	 */
	public function test_flatten( $array, $preserve_keys, $expected ) {
		$this->assertEquals( $expected, \Technote\Models\Utility::flatten( $array, $preserve_keys ) );
	}

	/**
	 * @return array
	 */
	public function _test_flatten_provider() {
		return array(
			array(
				array(),
				false,
				array(),
			),
			array(
				array(
					array( 'test1', 'test2' ),
					array( 'test3', 'test4' ),
				),
				false,
				array( 'test1', 'test2', 'test3', 'test4' ),
			),
			array(
				array(
					array( 'a' => 'test1', 'b' => 'test2' ),
					array( 'c' => 'test3', 'd' => 'test4' ),
				),
				false,
				array( 'test1', 'test2', 'test3', 'test4' ),
			),
			array(
				array(
					array( 'a' => 'test1', 'b' => 'test2' ),
					array( 'c' => 'test3', 'd' => 'test4' ),
				),
				true,
				array( 'a' => 'test1', 'b' => 'test2', 'c' => 'test3', 'd' => 'test4' ),
			),
			array(
				array(
					array( 'a' => 'test1', 'b' => 'test2' ),
					array( 'a' => 'test3', 'b' => 'test4' ),
				),
				true,
				array( 'a' => 'test3', 'b' => 'test4' ),
			),
		);
	}

	/**
	 * @dataProvider _test_array_get_provider
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @param mixed $expected
	 */
	public function test_array_get( $array, $key, $default, $expected ) {
		$this->assertEquals( $expected, \Technote\Models\Utility::array_get( $array, $key, $default ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_get_provider() {
		return array(
			array(
				array(),
				'test',
				null,
				null,
			),
			array(
				array(
					'test1' => true,
					'test2' => 100,
				),
				'test2',
				false,
				100,
			),
			array(
				array(
					'test1' => true,
					'test2' => 100,
				),
				'test3',
				false,
				false,
			),
		);
	}

	/**
	 * @dataProvider _test_array_set_provider
	 * @depends      test_array_get
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_array_set( $array, $key, $value ) {
		\Technote\Models\Utility::array_set( $array, $key, $value );
		$this->assertEquals( $value, \Technote\Models\Utility::array_get( $array, $key ) );
	}

	/**
	 * @return array
	 */
	public function _test_array_set_provider() {
		return array(
			array(
				array(),
				'test',
				null,
			),
			array(
				array(
					'test' => true,
				),
				'test',
				100,
			),
			array(
				array(
					'test' => true,
				),
				'test2',
				false,
			),
			array(
				array(
					'test' => true,
				),
				'test',
				array(
					'test1' => true,
				),
			),
		);
	}

	/**
	 * @dataProvider _test_replace_provider
	 *
	 * @param string $string
	 * @param array $data
	 * @param string $expected
	 */
	public function test_replace( $string, $data, $expected ) {
		$this->assertEquals( $expected, \Technote\Models\Utility::replace( $string, $data ) );
	}

	/**
	 * @return array
	 */
	public function _test_replace_provider() {
		return array(
			array(
				'test',
				array( 'a' => 'b' ),
				'test',
			),
			array(
				'test1${a}test2',
				array( 'a' => 'b' ),
				'test1btest2',
			),
			array(
				'test1${test}test2',
				array( 'test' => '' ),
				'test1test2',
			),
		);
	}
}