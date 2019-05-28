<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.0.0.0
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
 * Class Bigram
 * @package Related_Post\Classes\Models\Analyzer\Tokenizer
 */
class Bigram extends Tokenizer {

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
	 * @throws Exception
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
