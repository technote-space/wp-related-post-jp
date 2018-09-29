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
 * Class Igo
 * @package Related_Post\Models\Analyzer\Tokenizer
 */
class Igo extends \Related_Post\Models\Analyzer\Tokenizer {

	/** @var \Related_Post\Models\Analyzer\Igo $igo */
	private $igo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->igo = \Related_Post\Models\Analyzer\Igo::get_instance( $this->app );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	public function parse( $text ) {
		return $this->igo->count( $text );
	}

}
