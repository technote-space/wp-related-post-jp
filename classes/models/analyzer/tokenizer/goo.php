<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Tokenizer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Goo
 * @package Related_Post\Models\Analyzer\Tokenizer
 */
class Goo extends \Related_Post\Models\Analyzer\Tokenizer {

	/** @var \Related_Post\Models\Analyzer\Goo */
	private $goo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->goo = \Related_Post\Models\Analyzer\Goo::get_instance( $this->app );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 * @throws \Exception
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
