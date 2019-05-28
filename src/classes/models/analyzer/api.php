<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

use Exception;
use RuntimeException;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Api
 * @package Related_Post\Classes\Models\Analyzer
 */
abstract class Api implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

	/**
	 * @return string
	 */
	protected abstract function get_url();

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	protected abstract function get_params( $text, $classes );

	/**
	 * @return array
	 */
	protected function get_curl_options() {
		return [];
	}

	/**
	 * @param resource $ch
	 */
	protected function pre_send( $ch ) {

	}

	/**
	 * @param resource $ch
	 */
	protected function post_send( $ch ) {

	}

	/**
	 * @param string $res
	 *
	 * @return array
	 */
	protected abstract function parse_response( $res );

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	protected function get_post_fields( $params ) {
		return http_build_query( $params );
	}

	/**
	 * @param string $text
	 * @param array $classes
	 * @param int $trial
	 *
	 * @return array
	 * @throws Exception
	 */
	private function access( $text, $classes, $trial = 3 ) {
		try {
			$ch      = curl_init( $this->get_url() );
			$options = $this->get_curl_options();
			$options += [
				CURLOPT_POST           => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS     => $this->get_post_fields( $this->get_params( $text, $classes ) ),
			];
			curl_setopt_array( $ch, $options );

			$this->pre_send( $ch );
			$result = curl_exec( $ch );
			$this->post_send( $ch );

			$errno = curl_errno( $ch );
			$error = curl_error( $ch );

			curl_close( $ch );

			if ( CURLE_OK !== $errno ) {
				throw new RuntimeException( $error, $errno );
			}
			if ( false === $result ) {
				throw new Exception( $this->translate( 'Invalid API Response.' ) );
			}

			return $this->parse_response( $result );
		} catch ( Exception $e ) {
			if ( $trial > 0 ) {
				sleep( $this->get_retry_interval() );

				return $this->access( $text, $classes, $trial - 1 );
			}

			throw $e;
		}
	}

	/**
	 * @param $data
	 *
	 * @return array ( word => count )
	 */
	protected abstract function parse_data( $data );

	/**
	 * @return int
	 */
	protected abstract function get_retry_count();

	/**
	 * @return int
	 */
	protected abstract function get_retry_interval();

	/**
	 * @return int
	 */
	protected function get_text_limit() {
		return 0;
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 * @throws Exception
	 */
	public function count( $text, $classes = [] ) {
		$limit = $this->get_text_limit();
		if ( $limit > 0 ) {
			$text = substr( $text, 0, $limit );
		}

		return $this->parse_data( $this->access( $text, $classes, $this->get_retry_count() ) );
	}

}
