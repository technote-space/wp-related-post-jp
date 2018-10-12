<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer\Tokenizer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Yahoo
 * @package Related_Post\Models\Analyzer\Tokenizer
 */
class Yahoo extends \Related_Post\Models\Analyzer\Tokenizer {

	/** @var \Related_Post\Models\Analyzer\Yahoo */
	private $yahoo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->yahoo = \Related_Post\Models\Analyzer\Yahoo::get_instance( $this->app );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function parse( $text ) {
		return $this->yahoo->count( $text );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->yahoo->is_valid();
	}
}
