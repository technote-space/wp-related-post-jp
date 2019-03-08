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
 * Class Yahoo
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Yahoo extends \Related_Post\Classes\Models\Analyzer\Tokenizer {

	/** @var \Related_Post\Classes\Models\Analyzer\Yahoo */
	private $yahoo;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->yahoo = \Related_Post\Classes\Models\Analyzer\Yahoo::get_instance( $this->app );
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
