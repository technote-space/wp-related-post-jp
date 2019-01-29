<?php
/**
 * @version 1.1.3
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenizer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Goo
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Goo extends \Related_Post\Classes\Models\Analyzer\Tokenizer {

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
