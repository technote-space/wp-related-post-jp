<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: trivial change
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenizer;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Igo
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Igo extends \Related_Post\Classes\Models\Analyzer\Tokenizer {

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
