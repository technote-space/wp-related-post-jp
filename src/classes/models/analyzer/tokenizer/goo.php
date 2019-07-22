<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenizer;

use Exception;
use Related_Post\Classes\Models\Analyzer\Tokenizer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Goo
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Goo extends Tokenizer {

	/** @var \Related_Post\Classes\Models\Analyzer\Goo */
	private $goo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->goo = \Related_Post\Classes\Models\Analyzer\Goo::get_instance( $this->app );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 * @throws Exception
	 */
	public function parse( $text ) {
		return $this->goo->count( $text );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->goo->is_valid();
	}

}
