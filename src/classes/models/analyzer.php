<?php
/**
 * @version 1.1.3
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Analyzer
 * @package Related_Post\Classes\Models
 */
class Analyzer implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/**
	 * @param \WP_Post $post
	 *
	 * @return array ( word => count )
	 */
	public function parse( $post ) {
		return $this->parse_text( $this->get_length_filtered_extracted_result( $post ) );
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return mixed
	 */
	private function get_length_filtered_extracted_result( $post ) {
		$result     = $this->apply_filters( 'extractor_result', $this->extractor( $post ), $post );
		$max_length = $this->apply_filters( 'max_index_target_length' );
		if ( $max_length > 0 ) {
			$result = mb_substr( $result, 0, $max_length );
		}

		return $result;
	}

	/**
	 * @param string $text
	 *
	 * @return array ( word => count )
	 */
	public function parse_text( $text ) {
		$text = $this->apply_filters( 'char_filter_result', $this->char_filter( $text ), $text );
		list( $terms, $tokenizer ) = $this->apply_filters( 'tokenizer_result', $this->tokenizer( $text ), $text );

		return $this->apply_filters( 'token_filter_result', $this->token_filter( $terms, $tokenizer ), $terms );
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	private function extractor( $post ) {
		$filters = $this->apply_filters( 'extractor', $this->app->get_config( 'analyzer', 'extractor', '' ) );
		if ( empty( $filters ) ) {
			return '';
		}
		if ( ! is_array( $filters ) ) {
			$filters = [ $filters ];
		}

		foreach ( $filters as $filter ) {
			$filter = $this->app->define->plugin_namespace . '\\Classes\\Models\\Analyzer\\Extractor\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Classes\Models\Analyzer\Extractor' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Classes\Models\Analyzer\Extractor $instance */
				try {
					$instance = $filter::get_instance( $this->app );

					return $instance->extract( $post );
				} catch ( \Exception $e ) {
				}
			}
		}

		return $post->post_content;
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	private function char_filter( $text ) {
		$filters = $this->apply_filters( 'char_filters', $this->app->get_config( 'analyzer', 'char_filters', [] ) );
		if ( empty( $filters ) ) {
			return '';
		}
		if ( ! is_array( $filters ) ) {
			return $text;
		}

		foreach ( $filters as $filter ) {
			$filter = $this->app->define->plugin_namespace . '\\Classes\\Models\\Analyzer\\Charfilter\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Classes\Models\Analyzer\Charfilter' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Classes\Models\Analyzer\Charfilter $instance */
				$instance = $filter::get_instance( $this->app );
				$text     = $instance->filter( $text );
			}
		}

		return $text;
	}

	/**
	 * @param string $text
	 *
	 * @return array [array ( word => count ), tokenizer]
	 */
	private function tokenizer( $text ) {
		$filters = $this->apply_filters( 'tokenizer', $this->app->get_config( 'analyzer', 'tokenizer', '' ) );
		if ( empty( $filters ) ) {
			return [ [], '' ];
		}
		if ( ! is_array( $filters ) ) {
			$filters = [ $filters ];
		}

		foreach ( $filters as $filter ) {
			$tokenizer = $filter;
			$filter    = $this->app->define->plugin_namespace . '\\Classes\\Models\\Analyzer\\Tokenizer\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Classes\Models\Analyzer\Tokenizer' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Classes\Models\Analyzer\Tokenizer $instance */
				try {
					$instance = $filter::get_instance( $this->app );
					if ( $instance->is_valid() ) {
						return [ $instance->parse( $text ), $tokenizer ];
					}
				} catch ( \Exception $e ) {
				}
			}
		}

		return [ $this->default_tokenizer( $text ), '' ];
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	private function default_tokenizer( $text ) {
		$data = explode( ' ', $text );
		$ret  = [];
		foreach ( $data as $item ) {
			! isset( $ret[ $item ] ) and $ret[ $item ] = 0;
			$ret[ $item ] ++;
		}

		return $ret;
	}

	/**
	 * @param array $terms ( word => count )
	 * @param string $tokenizer
	 *
	 * @return array  ( word => count )
	 */
	private function token_filter( $terms, $tokenizer ) {
		$filters = $this->apply_filters( 'token_filters', $this->app->get_config( 'analyzer', 'token_filters', [] ) );
		if ( empty( $filters ) ) {
			return $terms;
		}
		if ( ! is_array( $filters ) ) {
			return $terms;
		}

		foreach ( $filters as $filter => $target ) {
			if ( is_int( $filter ) ) {
				$filter = $target;
				$target = false;
			} elseif ( ! is_array( $target ) ) {
				$target = [ $target ];
			}
			if ( false !== $target && ! in_array( $tokenizer, $target ) ) {
				continue;
			}
			$filter = $this->app->define->plugin_namespace . '\\Classes\\Models\\Analyzer\\Tokenfilter\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Classes\Models\Analyzer\Tokenfilter' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Classes\Models\Analyzer\Tokenfilter $instance */
				$instance = $filter::get_instance( $this->app );
				$terms    = $instance->filter( $terms );
			}
		}

		return $terms;
	}

}
