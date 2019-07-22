<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;
use WP_Framework_Db\Classes\Models\Query\Builder;
use WP_Post;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Bm25
 * @package Related_Post\Classes\Models
 */
class Bm25 implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

	/** @var Control $control */
	private $control;

	/** @var Analyzer $analyzer */
	private $analyzer;

	/** @var array $excluded */
	private $excluded;

	/**
	 * @var array $avg_dl_cache
	 */
	private $avg_dl_cache = [];

	/**
	 * @return Control
	 */
	private function get_control() {
		if ( ! isset( $this->control ) ) {
			$this->control = Control::get_instance( $this->app );
		}

		return $this->control;
	}

	/**
	 * @return Analyzer
	 */
	private function get_analyzer() {
		if ( ! isset( $this->analyzer ) ) {
			$this->analyzer = Analyzer::get_instance( $this->app );
		}

		return $this->analyzer;
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
			$this->delete_execute( $post_id, $word_ids, $post_types, $update_word_ids );
		} ) ) {
			if ( $init_ranking_flag && ! empty( $word_ids ) && ! empty( $post_types ) ) {
				$this->setup_ranking( $post_types, $word_ids );
			}
		}
	}

	/**
	 * @param int $post_id
	 * @param array $word_ids
	 * @param array $post_types
	 * @param array $update_word_ids
	 */
	private function delete_execute( $post_id, &$word_ids, &$post_types, $update_word_ids ) {
		$row = $this->table( 'document', 'd' )
			->alias_join_wp( 'posts', 'p', 'd.post_id', 'p.ID' )
			->select( [ 'd.document_id', 'p.post_type' ] )
			->where( 'd.post_id', $post_id )
			->row();

		if ( ! empty( $row ) ) {
			$document_id = $row['id'];
			$post_type   = $row['post_type'];
			$post_types  = $this->get_control()->get_post_types( $post_type );
			$word_ids    = $this->app->array->pluck( $this->table( 'rel_document_word' )->select( 'word_id' )->where( 'document_id', $document_id )->distinct()->get(), 'word_id' );
			$this->table( 'rel_document_word' )->where( 'document_id', $document_id )->delete();
			$this->table( 'document' )->where( 'post_id', $post_id )->delete();

			if ( ! empty( $post_types ) ) {
				$count_ln = $this->calc_n( $post_types ) - 1;
				// 更新しない word だけ 情報を更新
				// 更新するものは更新側で情報を更新
				$word_ids = array_diff( $word_ids, $update_word_ids );
				$nis      = $this->calc_nis( $post_types, $word_ids );
				foreach ( $word_ids as $word_id ) {
					$ni = $this->app->array->get( $nis, $word_id, 0 );
					$this->table( 'word' )->where_in( 'id', $word_ids )->update( [
						'count' => $ni,
						'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $count_ln, $ni ),
					] );
				}
			}
		}
	}

	/**
	 * @param WP_Post $post
	 * @param bool $update_word_now
	 *
	 * @return array
	 */
	public function update( $post, $update_word_now = true ) {
		$post_id    = $post->ID;
		$post_type  = $post->post_type;
		$post_types = $this->get_control()->get_post_types( $post_type );
		if ( empty( $post_types ) || 'publish' !== $post->post_status ) {
			return [];
		}

		$data     = $this->parse( $post );
		$word_ids = array_keys( $data );
		if ( ! empty( $word_ids ) ) {
			$word_ids = array_combine( $word_ids, $word_ids );
		}
		$total_count = array_sum( $data );

		if ( $this->app->db->transaction( function () use ( $post_id, $post_types, $data, $total_count, &$word_ids, $update_word_now ) {
			$word_ids = $this->update_execute( $post_id, $post_types, $data, $total_count, $word_ids, $update_word_now );
		} ) ) {
			// idf の変化がなくても tf は変化があるかもしれないため、この投稿は必ず更新
			$this->setup_ranking( $post_types, $word_ids, $post_id );
		}

		return [ $post_types, $word_ids ];
	}

	/**
	 * @param $post_id
	 * @param $post_types
	 * @param $data
	 * @param $total_count
	 * @param $word_ids
	 * @param $update_word_now
	 *
	 * @return array
	 */
	private function update_execute( $post_id, $post_types, $data, $total_count, $word_ids, $update_word_now ) {
		$this->delete( $post_id, $word_ids, false );
		$document_id = $this->table( 'document' )->insert( [
			'post_id' => $post_id,
			'count'   => $total_count,
		] );
		if ( count( $data ) > 0 ) {
			$max = max( $data );
			$this->table( 'rel_document_word' )->insert(
				$this->app->array->map( $data, function ( $count, $word_id ) use ( $document_id, $total_count, $max ) {
					return [
						'document_id' => $document_id,
						'word_id'     => $word_id,
						'count'       => $count,
						'tf'          => $this->calc_tf( $count, $total_count, $max ),
					];
				} )
			);
		}

		if ( ! empty( $word_ids ) ) {
			if ( $update_word_now ) {
				$word_ids = $this->update_word( $post_types, $word_ids );
			} else {
				$this->app->post->delete( $post_id, 'word_updated' );
			}
		}

		$this->app->post->set( $post_id, 'indexed', 1 );
		$this->app->post->delete( $post_id, 'setup_ranking' );

		return $word_ids;
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	public function update_word( $post_types, $word_ids = null ) {
		$this->app->db->transaction( function () use ( $post_types, &$word_ids ) {
			$count_ln = $this->calc_n( $post_types );
			if ( ! isset( $word_ids ) ) {
				$word_ids = $this->get_update_word_ids( $post_types, $count_ln );
			}

			$nis   = $this->calc_nis( $post_types, $word_ids );
			$words = $this->app->array->combine( $this->table( 'word' )->where_integer_in_raw( 'id', $word_ids )->select( [ 'word_id', 'count', 'idf' ] )->get(), 'word_id' );
			foreach ( $word_ids as $word_id ) {
				$ni  = $this->app->array->get( $nis, $word_id, 0 ) - 0;
				$row = $this->app->array->get( $words, $word_id, false );
				if ( empty( $row ) ) {
					$count = -1;
					$idf   = -1;
				} else {
					$count = $this->app->array->get( $row, 'count' ) - 0;
					$idf   = $this->app->array->get( $row, 'idf' ) - 0;
				}
				$new_c   = $ni;
				$new_idf = $ni <= 0 ? 0 : round( $this->calc_idf( $count_ln, $ni ), 6 );
				if ( $count !== $new_c || $idf !== $new_idf ) {
					$this->table( 'word' )->where( 'id', $word_id )->update( [
						'count' => $ni,
						'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $count_ln, $ni ),
					] );
				}
				if ( $idf === $new_idf ) {
					// idf の変化なし
					unset( $word_ids[ $word_id ] );
				}
			}
		} );

		return $word_ids;
	}

	/**
	 * @param array $post_types
	 * @param int $count_ln
	 *
	 * @return array
	 */
	private function get_update_word_ids( $post_types, $count_ln ) {
		$word_ids = [];
		$this->app->db->transaction( function () use ( $post_types, $count_ln, &$word_ids ) {
			$prev_n = (int) $this->get_control()->cache_get( 'document_count' );
			if ( $prev_n !== $count_ln ) {
				$this->get_control()->cache_set( 'document_count', $count_ln );
				$query    = $this->get_control()->from_document( $post_types )
					->select( 'd.post_id' )
					->distinct();
				$post_ids = $this->app->array->pluck( $query->get(), 'post_id' );
			} else {
				$query    = $this->get_control()->from_document( $post_types )
					->select( 'd.post_id' )
					->distinct()
					->where_not_exists( function ( $query ) {
						/** @var Builder $query */
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
				->get(), 'word_id'
			);
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
		if ( ! empty( $post_id ) && ! in_array( $post_id, $post_ids, true ) ) {
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
		$this->get_control()->cache_set( 'posts_indexed', false );

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
				$ranking         = $this->get_ranking( $post_id, $important_words, $post_types, false, true );
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
	 * @param WP_Post $post
	 * @param bool $register
	 *
	 * @return array ( word_id => count )
	 */
	public function parse( $post, $register = true ) {
		return $this->convert_word_data( $this->get_analyzer()->parse( $post ), $register );
	}

	/**
	 * @param string $text
	 * @param bool $register
	 *
	 * @return array ( word_id => count )
	 */
	public function parse_text( $text, $register = true ) {
		return $this->convert_word_data( $this->get_analyzer()->parse_text( $text ), $register );
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
				if ( ! empty( $word_id ) ) {
					$ret[ $word_id ] = $count;
				}
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
		if ( ! isset( $this->excluded ) ) {
			$this->excluded = $this->app->array->combine( $this->table( 'exclude_word' )->select( 'word' )->get(), 'word', 'word' );
		}

		return isset( $this->excluded[ $word ] );
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Tf%E2%80%93idf
	 *
	 * @param int $count
	 * @param int $total_count
	 * @param int $max
	 *
	 * @return float|int
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function calc_tf(
		/** @noinspection PhpUnusedParameterInspection */
		$count, $total_count, $max
	) {
		// term frequency
		return $count / $total_count;

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
		return $this->get_control()->from_document_word( $post_types )->distinct()->count( 'd.document_id' );
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	private function calc_nis( $post_types, $word_ids ) {
		$query = $this->get_control()->from_document_word( $post_types )
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
	 * @param int $count_ln
	 * @param int $count_n
	 *
	 * @return float
	 */
	private function calc_idf( $count_ln, $count_n ) {
		// inverse document frequency
		return log( $count_ln / $count_n, 1.5 );

		// inverse document frequency smooth
		// return log( 1 + $count_ln / $count_n, 1.5 );

		// probabilistic inverse document frequency
		// return log( $count_ln / $count_n - 1, 1.5 );
	}

	/**
	 * @param array $post_types
	 * @param array $word_ids
	 *
	 * @return array
	 */
	private function get_update_post_ids( $post_types, $word_ids ) {
		return $this->app->array->pluck( $this->get_control()->from_document_word( $post_types )
			->where_integer_in_raw( 'rw.word_id', $word_ids )
			->select( 'd.post_id' )
			->distinct()->get(), 'post_id'
		);
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
			] )->limit( $this->get_control()->get_important_words_count() )
			->order_by_desc( $this->raw( 'tfidf' ) )->get();
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 * @return float
	 */
	private function get_bm25_k1() {
		return 2.0;
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 * @return float
	 */
	private function get_bm25_b() {
		return 0.75;
	}

	/**
	 * @param array $post_types
	 *
	 * @return float
	 */
	private function calc_avg_dl( $post_types ) {
		$hash = sha1( wp_json_encode( $post_types ) );
		if ( ! isset( $this->avg_dl_cache[ $hash ] ) ) {
			$this->avg_dl_cache[ $hash ] = $this->get_control()->from_document( $post_types )
				->average( 'count' );
		}

		return $this->avg_dl_cache[ $hash ];
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 *
	 * @param int $post_id
	 * @param array $words
	 * @param array $post_types
	 * @param bool $is_search
	 * @param bool $is_exclude_post_ids
	 * @param bool $is_count
	 * @param int|null $count
	 * @param int|null $page
	 *
	 * @return array|int
	 */
	public function get_ranking( $post_id, $words, $post_types, $is_search, $is_exclude_post_ids, $is_count = false, $count = null, $page = null ) {
		if ( empty( $words ) ) {
			return [];
		}

		$subquery = $this->build_word_table( $words );
		if ( ! isset( $count ) ) {
			$count = $this->get_control()->get_ranking_count();
		}
		if ( isset( $page ) && $page > 1 ) {
			$offset = ( $page - 1 ) * $count;
		} else {
			$offset = null;
		}
		$threshold = $this->get_control()->get_score_threshold( $is_search );
		$param_k1  = $this->apply_filters( 'bm25_k1', $this->get_bm25_k1() );
		$param_b   = $this->apply_filters( 'bm25_b', $this->get_bm25_b() );
		$avgdl     = $this->apply_filters( 'avg_dl', $this->calc_avg_dl( $post_types ) );
		$select    = [ 'd.post_id' ];
		if ( $threshold > 0 || ! $is_count ) {
			$select[] = $this->raw( "SUM( w.idf * t.n * ( rw.tf * ( $param_k1 + 1 ) ) / ( rw.tf + $param_k1 * ( 1 - $param_b + $param_b * d.count / $avgdl ) ) ) as score" );
		}

		$query = $this->get_control()->from_document_word( $post_types, $is_exclude_post_ids )
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

	/**
	 * @param array $words
	 *
	 * @return Builder
	 */
	private function build_word_table( $words ) {
		$subquery = $this->builder();
		$first    = true;
		foreach ( $words as $word ) {
			$word_id = $word['word_id'];
			$count_n = $word['count'];
			$select  = [
				$this->raw( "{$word_id} as word_id" ),
				$this->raw( "{$count_n} as n" ),
			];
			if ( $first ) {
				$first = false;
				$subquery->select( $select );
			} else {
				$subquery->union( $this->builder()->select( $select ) );
			}
		}

		return $subquery;
	}

}
