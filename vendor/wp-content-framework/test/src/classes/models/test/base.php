<?php
/**
 * WP_Framework_Test Classes Models Test Base
 *
 * @version 0.0.1
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Test\Classes\Models\Test;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

if ( class_exists( '\PHPUnit\Framework\TestCase' ) ) {
	class Base extends \PHPUnit\Framework\TestCase {
	}
} else {
	class Base {
	}
}
