<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenizer;

use Related_Post\Classes\Models\Analyzer\Tokenizer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Igo
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Igo extends Tokenizer {

	/** @var \Related_Post\Classes\Models\Analyzer\Igo $igo */
	private $igo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->igo = \Related_Post\Classes\Models\Analyzer\Igo::get_instance( $this->app );
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
