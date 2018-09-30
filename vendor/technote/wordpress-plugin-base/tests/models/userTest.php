<?php
/**
 * Technote Models User Test
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
 * Class UserTest
 * @package Technote\Tests\Models
 * @group technote
 * @group models
 */
class UserTest extends \Technote\Tests\TestCase {

	/** @var \Technote\Models\User */
	private static $user;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::$user = \Technote\Models\User::get_instance( static::$app );
		foreach ( static::get_test_value() as $value ) {
			static::$user->delete( $value[0], 1 );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		foreach ( static::get_test_value() as $value ) {
			static::$user->delete( $value[0], 1 );
		}
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_set( $key, $value ) {
		$this->assertEquals( true, static::$user->set( $key, $value, 1 ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_get( $key, $value ) {
		$this->assertEquals( $value, static::$user->get( $key, 1 ) );
	}

	/**
	 * @dataProvider _test_value_provider
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function test_delete(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $value
	) {
		$this->assertEquals( true, static::$user->delete( $key, 1 ) );
		$this->assertEquals( '', static::$user->get( $key, 1 ) );
	}

	/**
	 * @return array
	 */
	private static function get_test_value() {
		return array(
			array( 'technote_test_user_bool', true ),
			array( 'technote_test_user_int', 123 ),
			array( 'technote_test_user_float', 0.987 ),
			array( 'technote_test_user_string', 'test' ),
			array(
				'technote_test_user_array',
				array(
					'test1' => 'test1',
					'test2' => 2,
					'test3' => false,
				)
			),
			array( 'technote_test_user_null', null ),
		);
	}

	/**
	 * @return array
	 */
	public function _test_value_provider() {
		return static::get_test_value();
	}

}