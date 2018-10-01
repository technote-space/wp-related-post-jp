<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Analyzer
 * @package Related_Post\Models
 */
class Analyzer implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/**
	 * @param \WP_Post $post
	 *
	 * @return array ( word => count )
	 */
	public function parse( $post ) {
		return $this->parse_text( $this->apply_filters( 'extractor_result', $this->extractor( $post ), $post ) );
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
			$filters = array( $filters );
		}

		foreach ( $filters as $filter ) {
			$filter = $this->app->define->plugin_namespace . '\\Models\\Analyzer\\Extractor\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Models\Analyzer\Extractor' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Models\Analyzer\Extractor $instance */
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
		$filters = $this->apply_filters( 'char_filters', $this->app->get_config( 'analyzer', 'char_filters', array() ) );
		if ( empty( $filters ) ) {
			return '';
		}
		if ( ! is_array( $filters ) ) {
			return $text;
		}

		foreach ( $filters as $filter ) {
			$filter = $this->app->define->plugin_namespace . '\\Models\\Analyzer\\Charfilter\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Models\Analyzer\Charfilter' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Models\Analyzer\Charfilter $instance */
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
			return array( array(), '' );
		}
		if ( ! is_array( $filters ) ) {
			$filters = array( $filters );
		}

		foreach ( $filters as $filter ) {
			$tokenizer = $filter;
			$filter    = $this->app->define->plugin_namespace . '\\Models\\Analyzer\\Tokenizer\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Models\Analyzer\Tokenizer' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Models\Analyzer\Tokenizer $instance */
				try {
					$instance = $filter::get_instance( $this->app );
					if ( $instance->is_valid() ) {
						return array( $instance->parse( $text ), $tokenizer );
					}
				} catch ( \Exception $e ) {
				}
			}
		}

		return array( $this->default_tokenizer( $text ), '' );
	}

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	private function default_tokenizer( $text ) {
		$data = explode( ' ', $text );
		$ret  = array();
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
		$filters = $this->apply_filters( 'token_filters', $this->app->get_config( 'analyzer', 'token_filters', array() ) );
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
				$target = array( $target );
			}
			if ( false !== $target && ! in_array( $tokenizer, $target ) ) {
				continue;
			}
			$filter = $this->app->define->plugin_namespace . '\\Models\\Analyzer\\Tokenfilter\\' . ucfirst( $filter );
			if ( is_subclass_of( $filter, '\Related_Post\Models\Analyzer\Tokenfilter' ) ) {
				/** @var \Technote\Interfaces\Singleton $filter */
				/** @var \Related_Post\Models\Analyzer\Tokenfilter $instance */
				$instance = $filter::get_instance( $this->app );
				$terms    = $instance->filter( $terms );
			}
		}

		return $terms;
	}

}
