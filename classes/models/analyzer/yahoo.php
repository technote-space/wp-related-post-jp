<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models\Analyzer;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Yahoo
 * @package Related_Post\Models\Analyzer
 */
class Yahoo extends Api {

	/**
	 * @return bool
	 */
	public function is_valid() {
		$app_id = $this->apply_filters( 'yahoo_client_id' );

		return ! empty( $app_id );
	}

	/**
	 * @return string
	 */
	protected function get_url() {
		return $this->apply_filters( 'yahoo_api_url', 'https://jlp.yahooapis.jp/MAService/V1/parse' );
	}

	/**
	 * @param string $text
	 * @param array $classes
	 *
	 * @return array
	 */
	protected function get_params( $text, $classes ) {
		return array(
			'sentence' => $text,
			'results'  => 'uniq',
			'response' => 'surface',
			'filter'   => $this->class_to_filter( $classes ),
		);
	}

	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	private function class_to_filter( $classes ) {
		$map = array(
			'形容詞'  => 1,
			'形容動詞' => 2,
			'感動詞'  => 3,
			'副詞'   => 4,
			'連体詞'  => 5,
			'接続詞'  => 6,
			'接頭辞'  => 7,
			'接尾辞'  => 8,
			'名詞'   => 9,
			'動詞'   => 10,
			'助詞'   => 11,
			'助動詞'  => 12,
			'特殊'   => 13,
		);

		$ret = implode( ',', array_unique( array_filter( array_map( function ( $class ) use ( $map ) {
			return isset( $map[ $class ] ) ? $map[ $class ] : false;
		}, $classes ), function ( $d ) {
			return false !== $d;
		} ) ) );
		"" === $ret and $ret = $map['名詞'];

		return $ret;
	}

	/**
	 * @return array
	 */
	protected function get_curl_options() {
		$app_id = $this->apply_filters( 'yahoo_client_id' );

		return array(
			CURLOPT_USERAGENT => "Yahoo AppID: $app_id",
		);
	}

	/**
	 * @param string $res
	 *
	 * @return array
	 * @throws \Exception
	 */
	protected function parse_response( $res ) {
		$xml   = simplexml_load_string( $res );
		$json  = json_encode( $xml );
		$array = json_decode( $json, true );
		if ( ! isset( $array['uniq_result']['word_list']['word'] ) ) {
			throw new \Exception( $this->app->translate( 'Invalid API Response.' ) );
		}

		return $array['uniq_result']['word_list']['word'];
	}

	/**
	 * @param $data
	 *
	 * @return array
	 */
	protected function parse_data( $data ) {
		$data = array_filter( $data, function ( $d ) {
			return isset( $d['surface'], $d['count'] );
		} );

		return array_combine( array_map( function ( $d ) {
			return $d['surface'];
		}, $data ), array_map( function ( $d ) {
			return $d['count'];
		}, $data ) );
	}

	/**
	 * @return int
	 */
	protected function get_retry_count() {
		return $this->apply_filters( 'yahoo_retry_count' );
	}

	/**
	 * @return int
	 */
	protected function get_retry_interval() {
		return $this->apply_filters( 'yahoo_retry_interval' );
	}

	/**
	 * @return int
	 */
	protected function get_text_limit() {
		return 100 * 1024 - 1000; // 100KB - sentence以外のリクエスト分を適当にマイナス
	}

}
