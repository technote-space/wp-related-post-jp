<?php
/**
 * @version 1.3.9
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Added: 除外ワード (#22)
 * @since 1.3.9 #51, wp-content-framework/db#9, wp-content-framework/common#44
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Bm25
 * @package Related_Post\Classes\Models
 */
class Bm25 implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Core\Traits\Hook, \WP_Framework_Common\Traits\Package;

	/** @var Control $control */
	private $control;

	/** @var Analyzer $analyzer */
	private $analyzer;

	/** @var array $_excluded */
	private $_excluded;

	/**
	 * @var array $_avg_dl_cache
	 */
	private $_avg_dl_cache = [];

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->control  = Control::get_instance( $this->app );
		$this->analyzer = Analyzer::get_instance( $this->app );
	}

	/**
	 * @param int $post_id
	 * @param array $update_word_ids
	 * @param bool $init_ranking_flag
	 */
	public function delete( $post_id, $update_word_ids = [], $init_ranking_flag = true ) {
		$word_ids   = [];
		$post_types = [];
		if ( $this->app->db->transaction( function () use ( $post_id, &$word_ids, &$post_types, $update_word_ids ) {
			$row = $this->table( 'document', 'd' )
			            ->alias_join_wp( 'posts', 'p', 'd.post_id', 'p.ID' )
			            ->select( [ 'd.document_id', 'p.post_type' ] )
			            ->where( 'd.post_id', $post_id )
			            ->row();

			if ( ! empty( $row ) ) {
				$document_id = $row['id'];
				$post_type   = $row['post_type'];
				$post_types  = $this->control->get_post_types( $post_type );
				$word_ids    = $this->app->array->pluck( $this->table( 'rel_document_word' )->select( 'word_id' )->where( 'document_id', $document_id )->distinct()->get(), 'word_id' );
				$this->table( 'rel_document_word' )->where( 'document_id', $document_id )->delete();
				$this->table( 'document' )->where( 'post_id', $post_id )->delete();

				if ( ! empty( $post_types ) ) {
					$N = $this->calc_n( $post_types ) - 1;
					// 更新しない word だけ 情報を更新
					// 更新するものは更新側で情報を更新
					$word_ids = array_diff( $word_ids, $update_word_ids );
					$nis      = $this->calc_nis( $post_types, $word_ids );
					foreach ( $word_ids as $word_id ) {
						$ni = $this->app->array->get( $nis, $word_id, 0 );
						$this->table( 'word' )->where_in( 'id', $word_ids )->update( [
							'count' => $ni,
							'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
						] );
					}
				}
			}

		} ) ) {
			if ( $init_ranking_flag && ! empty( $word_ids ) && ! empty( $post_types ) ) {
				$this->setup_ranking( $post_types, $word_ids );
			}
		}
	}

	/**
	 * @param \WP_Post $post
	 * @param bool $update_word_now
	 *
	 * @return array
	 */
	public function update( $post, $update_word_now = true ) {
		$post_id    = $post->ID;
		$post_type  = $post->post_type;
		$post_types = $this->control->get_post_types( $post_type );
		if ( empty( $post_types ) || 'publish' !== $post->post_status ) {
			return [];
		}

		$data     = $this->parse( $post );
		$word_ids = array_keys( $data );
		! empty( $word_ids ) and $word_ids = array_combine( $word_ids, $word_ids );
		$dl = array_sum( $data );

		if ( $this->app->db->transaction( function () use ( $post_id, $post_type, $post_types, $data, $dl, &$word_ids, $update_word_now ) {
			$this->delete( $post_id, $word_ids, false );
			$document_id = $this->table( 'document' )->insert( [
				'post_id' => $post_id,
				'count'   => $dl,
			] );
			if ( count( $data ) > 0 ) {
				$max = max( $data );
				$this->table( 'rel_document_word' )->insert(
					$this->app->array->map( $data, function ( $count, $word_id ) use ( $document_id, $dl, $max ) {
						return [
							'document_id' => $document_id,
							'word_id'     => $word_id,
							'count'       => $count,
							'tf'          => $this->calc_tf( $count, $dl, $max ),
						];
					} )
				);
			}

			if ( $update_word_now ) {
				$word_ids = $this->update_word( $post_types, $word_ids );
			} else {
				if ( ! empty( $word_ids ) ) {
					$this->app->post->delete( $post_id, 'word_updated' );
				}
			}
			$this->app->post->set( $post_id, 'indexed', 1 );
			$this->app->post->delete( $post_id, 'setup_ranking' );
		} ) ) {
			// idf の変化がなくても tf は変化があるかもしれないため、この投稿は必ず更新
			$this->setup_ranking( $post_types, $word_ids, $post_id );
		}

		return [ $post_types, $word_ids ];
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	public function update_word( $post_types, $word_ids = null ) {
		$this->app->db->transaction( function () use ( $post_types, &$word_ids ) {
			$N = $this->calc_n( $post_types );
			if ( ! isset( $word_ids ) ) {
				$word_ids = $this->get_update_word_ids( $post_types, $N );
			}

			$nis   = $this->calc_nis( $post_types, $word_ids );
			$words = $this->app->array->combine( $this->table( 'word' )->where_integer_in_raw( 'id', $word_ids )->select( [ 'word_id', 'count', 'idf' ] )->get(), 'word_id' );
			foreach ( $word_ids as $word_id ) {
				$ni  = $this->app->array->get( $nis, $word_id, 0 );
				$row = $this->app->array->get( $words, $word_id, false );
				if ( empty( $row ) ) {
					$c   = -1;
					$idf = -1;
				} else {
					$c   = $this->app->array->get( $row, 'count' );
					$idf = $this->app->array->get( $row, 'idf' );
				}
				$new_c   = $ni;
				$new_idf = $ni <= 0 ? 0 : round( $this->calc_idf( $N, $ni ), 6 );
				if ( $c != $new_c || $idf != $new_idf ) {
					$this->table( 'word' )->where( 'id', $word_id )->update( [
						'count' => $ni,
						'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
					] );
				}
				if ( $idf == $new_idf ) {
					// idf の変化なし
					unset( $word_ids[ $word_id ] );
				}
			}

		} );

		return $word_ids;
	}

	/**
	 * @param array $post_types
	 * @param \WP_Framework_Db\Classes\Models\Query\Builder $query
	 *
	 * @return \WP_Framework_Db\Classes\Models\Query\Builder
	 */
	private function from_common( $post_types, \WP_Framework_Db\Classes\Models\Query\Builder $query ) {
		return $this->control->common_filter( $post_types, $query->alias_join_wp( 'posts', 'p', 'd.post_id', 'p.ID' ) );
	}

	/**
	 * @param array $post_types
	 *
	 * @return \WP_Framework_Db\Classes\Models\Query\Builder
	 */
	private function from_document( $post_types ) {
		return $this->from_common( $post_types, $this->table( 'document', 'd' ) );
	}

	/**
	 * @param array $post_types
	 *
	 * @return \WP_Framework_Db\Classes\Models\Query\Builder
	 */
	private function from_document_word( $post_types ) {
		return $this->from_common( $post_types, $this->table( 'rel_document_word', 'rw' )
		                                             ->alias_join( 'document', 'd', 'd.document_id', 'rw.document_id' ) );
	}

	/**
	 * @param array $post_types
	 * @param int $N
	 *
	 * @return array
	 */
	private function get_update_word_ids( $post_types, $N ) {
		$word_ids = [];
		$this->app->db->transaction( function () use ( $post_types, $N, &$word_ids ) {
			$prev_N = $this->cache_get( 'document_count' );
			if ( $prev_N != $N ) {
				$this->cache_set( 'document_count', $N );
				$query    = $this->from_document( $post_types )
				                 ->select( 'd.post_id' )
				                 ->distinct();
				$post_ids = $this->app->array->pluck( $query->get(), 'post_id' );
			} else {
				$query    = $this->from_document( $post_types )
				                 ->select( 'd.post_id' )
				                 ->distinct()
				                 ->where_not_exists( function ( $query ) {
					                 /** @var \WP_Framework_Db\Classes\Models\Query\Builder $query */
					                 $query->table( $this->get_wp_table( 'postmeta', 'pm' ) )
					                       ->where_column( 'pm.post_id', 'd.post_id' )
					                       ->where( 'pm.meta_key', $this->app->post->get_meta_key( 'word_updated' ) )
					                       ->select_raw( '"X"' );
				                 } );
				$post_ids = $this->app->array->pluck( $query->get(), 'post_id' );
			}

			$word_ids = $this->app->array->pluck( $this->table( 'rel_document_word', 'rw' )
			                                           ->alias_join( 'document', 'd', 'd.document_id', 'rw.document_id' )
			                                           ->alias_join_wp( 'posts', 'p', 'd.post_id', 'p.ID' )
			                                           ->where_integer_in_raw( 'd.post_id', $post_ids )
			                                           ->where( 'p.post_status', 'publish' )
			                                           ->select( 'rw.word_id' )
			                                           ->distinct()
			                                           ->get(), 'word_id' );
			foreach ( $post_ids as $post_id ) {
				$this->app->post->set( $post_id, 'word_updated', 1 );
			}
		} );

		return $word_ids;
	}

	/**
	 * @param array $post_types
	 * @param array $word_ids
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function setup_ranking( $post_types, $word_ids, $post_id = null ) {
		$post_ids = $this->get_update_post_ids( $post_types, $word_ids );
		if ( ! empty( $post_id ) && ! in_array( $post_id, $post_ids ) ) {
			$post_ids[] = $post_id;
		}

		return $this->update_rankings( $post_ids, $post_types );
	}

	/**
	 * @param array $post_ids
	 * @param array $post_types
	 *
	 * @return bool
	 */
	private function update_rankings( $post_ids, $post_types ) {
		foreach ( $post_ids as $post_id ) {
			$this->update_ranking( $post_id, $post_types, false );
		}
		$this->control->cache_set( 'posts_indexed', false );

		return true;
	}

	/**
	 * @param int $post_id
	 * @param array $post_types
	 * @param bool $update_ranking_now
	 *
	 * @return bool
	 */
	public function update_ranking( $post_id, $post_types, $update_ranking_now ) {
		return $this->app->db->transaction( function () use ( $post_id, $post_types, $update_ranking_now ) {
			if ( $update_ranking_now ) {
				$important_words = $this->get_important_words( $post_id );
				$ranking         = $this->get_ranking( $post_id, $important_words, $post_types, false );

				$this->table( 'ranking' )->where( 'post_id', $post_id )->delete();
				$this->table( 'ranking' )->insert( $this->app->array->map( $ranking, function ( $item ) use ( $post_id ) {
					return [
						'post_id'      => $post_id,
						'rank_post_id' => $item['post_id'],
						'score'        => $item['score'],
					];
				} ) );
				$this->app->post->set( $post_id, 'setup_ranking', 1 );
			} else {
				$this->app->post->delete( $post_id, 'setup_ranking' );
			}
		} );
	}

	/**
	 * @param \WP_Post $post
	 * @param bool $register
	 *
	 * @return array ( word_id => count )
	 */
	public function parse( $post, $register = true ) {
		return $this->convert_word_data( $this->analyzer->parse( $post ), $register );
	}

	/**
	 * @param string $text
	 * @param bool $register
	 *
	 * @return array ( word_id => count )
	 */
	public function parse_text( $text, $register = true ) {
		return $this->convert_word_data( $this->analyzer->parse_text( $text ), $register );
	}

	/**
	 * @param array $data
	 * @param bool $register
	 *
	 * @return array ( word_id => count )
	 */
	private function convert_word_data( $data, $register ) {
		$words = array_keys( $data );
		$words = $this->app->array->combine( $this->table( 'word' )->where_in( 'word', $words )->select( [ 'id', 'word' ] )->get(), 'word' );
		$ret   = [];
		$this->app->db->transaction( function () use ( $data, $register, $words, &$ret ) {
			foreach ( $data as $word => $count ) {
				$row     = $this->app->array->get( $words, $word );
				$word_id = $this->word_to_id( $word, $row, $register );
				! empty( $word_id ) and $ret[ $word_id ] = $count;
			}
		} );

		return $ret;
	}

	/**
	 * @param string $word
	 * @param array|null $row
	 * @param int $register
	 *
	 * @return int
	 */
	private function word_to_id( $word, $row, $register ) {
		if ( $this->is_excluded( $word ) ) {
			return 0;
		}
		if ( empty( $row ) ) {
			if ( $register ) {
				$word_id = $this->table( 'word' )->insert( [
					'word' => $word,
				] );
			} else {
				$word_id = 0;
			}
		} else {
			$word_id = $row['id'];
		}

		return $word_id;
	}

	/**
	 * @param string $word
	 *
	 * @return bool
	 */
	public function is_excluded( $word ) {
		if ( ! isset( $this->_excluded ) ) {
			$this->_excluded = $this->app->array->combine( $this->table( 'exclude_word' )->select( 'word' )->get(), 'word', 'word' );
		}

		return isset( $this->_excluded[ $word ] );
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Tf%E2%80%93idf
	 *
	 * @param int $count
	 * @param int $dl
	 * @param int $max
	 *
	 * @return float|int
	 */
	private function calc_tf(
		/** @noinspection PhpUnusedParameterInspection */
		$count, $dl, $max
	) {
		// term frequency
		return $count / $dl;

		// raw count
		// return $count;

		// log normalization
		// return log( 1 + $count );

		// double normalization 0.5
		// return 0.5 + 0.5 * $count / $max;
	}

	/**
	 * @param array $post_types
	 *
	 * @return int
	 */
	private function calc_n( $post_types ) {
		return $this->from_document_word( $post_types )->distinct()->count( 'd.document_id' );
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	private function calc_nis( $post_types, $word_ids ) {
		$query = $this->from_document_word( $post_types )
		              ->alias_join( 'word', 'w', 'rw.word_id', 'w.word_id' )
		              ->select( [
			              'rw.word_id',
			              $this->raw( 'COUNT(DISTINCT d.document_id) as N' ),
		              ] )
		              ->group_by( 'rw.word_id' );
		if ( isset( $word_ids ) ) {
			$query->where_integer_in_raw( 'rw.word_id', $word_ids );
		}

		return $this->app->array->combine( $query->get(), 'word_id', 'N' );
	}

	/**
	 * @param int $N
	 * @param int $n
	 *
	 * @return float
	 */
	private function calc_idf( $N, $n ) {
		// inverse document frequency
		return log( $N / $n, 1.5 );

		// inverse document frequency smooth
		// return log( 1 + $N / $n, 1.5 );

		// probabilistic inverse document frequency
		// return log( $N / $n - 1, 1.5 );
	}

	/**
	 * @param array $post_types
	 * @param array $word_ids
	 *
	 * @return array
	 */
	private function get_update_post_ids( $post_types, $word_ids ) {
		return $this->app->array->pluck( $this->from_document_word( $post_types )
		                                      ->where_integer_in_raw( 'rw.word_id', $word_ids )
		                                      ->select( 'd.post_id' )
		                                      ->distinct()->get(), 'post_id' );
	}

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_important_words( $post_id ) {
		return $this->table( 'rel_document_word', 'rw' )
		            ->alias_join( 'document', 'd', 'rw.document_id', 'd.document_id' )
		            ->alias_join( 'word', 'w', 'rw.word_id', 'w.word_id' )
		            ->where( 'd.post_id', $post_id )
		            ->select( [
			            'rw.word_id',
			            $this->raw( 'rw.tf * w.idf as tfidf' ),
			            'rw.count',
			            'rw.tf',
			            'w.word',
			            'w.idf',
		            ] )->limit( $this->control->get_important_words_count() )
		            ->order_by_desc( $this->raw( 'tfidf' ) )->get();
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 * @return float
	 */
	private function get_bm25_k1() {
		return 2.0;
		// return 1.2;
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 * @return float
	 */
	private function get_bm25_b() {
		return 0.75;
	}

	/**
	 * @param $post_types
	 *
	 * @return float
	 */
	private function calc_avg_dl( $post_types ) {
		$hash = sha1( json_encode( $post_types ) );
		if ( ! isset( $this->_avg_dl_cache[ $hash ] ) ) {
			$this->_avg_dl_cache[ $hash ] = $this->from_document( $post_types )
			                                     ->average( 'count' );
		}

		return $this->_avg_dl_cache[ $hash ];
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 *
	 * @param int $post_id
	 * @param array $words
	 * @param array $post_types
	 * @param bool $is_search
	 * @param bool $is_count
	 * @param int|null $count
	 * @param int|null $page
	 *
	 * @return array|int
	 */
	public function get_ranking( $post_id, $words, $post_types, $is_search, $is_count = false, $count = null, $page = null ) {
		if ( empty( $words ) ) {
			return [];
		}

		$subquery = $this->builder();
		$first    = true;
		foreach ( $words as $word ) {
			$word_id = $word['word_id'];
			$n       = $word['count'];
			$select  = [
				$this->raw( "{$word_id} as word_id" ),
				$this->raw( "{$n} as n" ),
			];
			if ( $first ) {
				$first = false;
				$subquery->select( $select );
			} else {
				$subquery->union( $this->builder()->select( $select ) );
			}
		}

		! isset( $count ) and $count = $this->control->get_ranking_count();
		if ( isset( $page ) && $page > 1 ) {
			$offset = ( $page - 1 ) * $count;
		} else {
			$offset = null;
		}
		$threshold = $this->control->get_score_threshold( $is_search );
		$k1        = $this->apply_filters( 'bm25_k1', $this->get_bm25_k1() );
		$b         = $this->apply_filters( 'bm25_b', $this->get_bm25_b() );
		$avgdl     = $this->apply_filters( 'avg_dl', $this->calc_avg_dl( $post_types ) );

		$select = [ 'd.post_id' ];
		if ( $threshold > 0 || ! $is_count ) {
			$select[] = $this->raw( "SUM( w.idf * t.n * ( rw.tf * ( $k1 + 1 ) ) / ( rw.tf + $k1 * ( 1 - $b + $b * d.count / $avgdl ) ) ) as score" );
		}

		$query = $this->from_document_word( $post_types )
		              ->alias_join( 'word', 'w', 'rw.word_id', 'w.word_id' )
		              ->join_sub( $subquery, 't', 't.word_id', 'rw.word_id' )
		              ->where( 'd.post_id', '!=', $post_id )
		              ->select( $select )
		              ->group_by( 'd.post_id' );

		if ( $threshold > 0 ) {
			$max_query       = clone $query;
			$max             = $this->app->array->get( $max_query->order_by_desc( $this->raw( 'score' ) )->row(), 'score' );
			$threshold_score = $max * $threshold;
			$query->having( $this->raw( 'score' ), '>=', $threshold_score );
		}
		$query = $this->builder()->from_sub( $query, 't' );

		return $is_count ? $query->count() : $query->order_by_desc( $this->raw( 'score' ) )->limit( $count )->offset( $offset )->get();
	}

}
