<?php
/**
 * WP_Framework_Test Tests Base
 *
 * @version 0.0.5
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Test\Classes\Tests;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Base
 * @package WP_Framework_Test\Classes\Tests
 */
abstract class Base extends \WP_Framework_Test\Classes\Models\Test\Base implements \WP_Framework_Test\Interfaces\Test {

	use \WP_Framework_Test\Traits\Test, \WP_Framework_Test\Traits\Package;

	/** @var \WP_Framework */
	protected static $test_app;

	/**
	 * @param \WP_Framework $app
	 */
	public static function set_app( $app ) {
		static::$test_app = $app;
	}

	/**
	 * @throws \ReflectionException
	 */
	public final function setUp() {
		$class = get_called_class();
		if ( false === $class ) {
			$class = get_class();
		}
		$reflection = new \ReflectionClass( $class );
		$this->init( static::$test_app, $reflection );
		$this->_setup();
	}

	/**
	 * setup
	 */
	public function _setup() {

	}
}
