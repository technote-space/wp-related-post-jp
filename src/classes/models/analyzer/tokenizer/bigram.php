<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Tokenizer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Bigram
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Bigram extends \Related_Post\Classes\Models\Analyzer\Tokenizer {

	/** @var \Related_Post\Classes\Models\Analyzer\Bigram */
	private $bigram;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->bigram = \Related_Post\Classes\Models\Analyzer\Bigram::get_instance( $this->app );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function parse( $text ) {
		return $this->bigram->count( $text );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return ! empty( $this->app->get_option( 'use_bigram_tokenizer' ) );
	}
}
