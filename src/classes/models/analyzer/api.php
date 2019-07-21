<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

use Exception;
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
	abstract protected function get_url();

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	abstract protected function get_params( $text, $classes );

	/**
	 * @return array
	 */
	protected function get_additional_post_options() {
		return [];
	}

	/**
	 * @param string $res
	 *
	 * @return array
	 */
	abstract protected function parse_response( $res );

	/**
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function get_post_fields( $params ) {
		return $params;
	}

	/**
	 * @return string
	 */
	protected function get_data_format() {
		return 'query';
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
			$response = wp_remote_post( $this->get_url(), $this->get_post_options( $text, $classes ) );
			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			if ( empty( $response['response']['code'] ) || 200 !== $response['response']['code'] ) {
				throw new Exception( $response['response']['message'] );
			}

			return $this->parse_response( $response['body'] );
		} catch ( Exception $e ) {
			if ( $trial > 0 ) {
				sleep( $this->get_retry_interval() );

				return $this->access( $text, $classes, $trial - 1 );
			}

			throw $e;
		}
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	private function get_post_options( $text, $classes ) {
		$options = $this->get_additional_post_options();
		$options = array_replace_recursive( $options, [
			'body'        => $this->get_post_fields( $this->get_params( $text, $classes ) ),
			'data_format' => $this->get_data_format(),
		] );

		return $options;
	}

	/**
	 * @param $data
	 *
	 * @return array ( word => count )
	 */
	abstract protected function parse_data( $data );

	/**
	 * @return int
	 */
	abstract protected function get_retry_count();

	/**
	 * @return int
	 */
	abstract protected function get_retry_interval();

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
