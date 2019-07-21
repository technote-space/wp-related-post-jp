<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer;

use Exception;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Goo
 * @package Related_Post\Classes\Models\Analyzer
 */
class Goo extends Api {

	/**
	 * @return bool
	 */
	public function is_valid() {
		$app_id = $this->apply_filters( 'goo_app_id' );

		return ! empty( $app_id );
	}

	/**
	 * @return string
	 */
	protected function get_url() {
		return $this->apply_filters( 'goo_api_url', 'https://labs.goo.ne.jp/api/morph' );
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	protected function get_params( $text, $classes ) {
		return [
			'app_id'      => $this->apply_filters( 'goo_app_id' ),
			'sentence'    => $text,
			'info_filter' => 'form',
			'pos_filter'  => $this->class_to_filter( $classes ),
		];
	}

	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	private function class_to_filter( $classes ) {
		if ( empty( $classes ) ) {
			return '名詞';
		}

		return implode( '|', $classes );
	}

	/**
	 * @return array
	 */
	protected function get_curl_options() {
		return [
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
			],
		];
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	protected function get_post_fields( $params ) {
		return json_encode( $params );
	}

	/**
	 * @param string $res
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function parse_response( $res ) {
		$array = json_decode( $res, true );
		if ( ! isset( $array['word_list'] ) ) {
			throw new Exception( $this->translate( 'Invalid API Response.' ) );
		}

		return $array['word_list'];
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	protected function parse_data( $data ) {
		$data = $this->app->array->flatten( $data );
		$ret  = [];
		foreach ( $data as $word ) {
			! isset( $ret[ $word ] ) and $ret[ $word ] = 0;
			$ret[ $word ] ++;
		}

		return $ret;
	}

	/**
	 * @return int
	 */
	protected function get_retry_count() {
		return $this->apply_filters( 'goo_retry_count' );
	}

	/**
	 * @return int
	 */
	protected function get_retry_interval() {
		return $this->apply_filters( 'goo_retry_interval' );
	}

}
