<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Added: 除外ワード (#22)
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

			/** @var \wpdb $wpdb */
			global $wpdb;
			$row = $this->app->db->select_row( [
				[ 'document', 'd' ],
				[
					[ $wpdb->posts, 'p' ],
					'INNER JOIN',
					[
						'd.post_id',
						'=',
						'p.ID',
					],
				],
			], [
				'd.post_id' => $post_id,
			], [ 'd.document_id' => 'id', 'p.post_type' ] );

			if ( ! empty( $row ) ) {
				$document_id = $row['id'];
				$post_type   = $row['post_type'];
				$post_types  = $this->control->get_post_types( $post_type );
				$word_ids    = $this->app->utility->array_pluck( $this->app->db->select( 'rel_document_word', [
					'document_id' => $document_id,
				], 'DISTINCT word_id' ), 'word_id' );
				$this->app->db->delete( 'rel_document_word', [
					'document_id' => $document_id,
				] );
				$this->app->db->delete( 'document', [
					'post_id' => $post_id,
				] );

				if ( ! empty( $post_types ) ) {
					$N = $this->calc_n( $post_types ) - 1;
					// 更新しない word だけ 情報を更新
					// 更新するものは更新側で情報を更新
					$word_ids = array_diff( $word_ids, $update_word_ids );
					$nis      = $this->calc_nis( $post_types, $word_ids );
					foreach ( $word_ids as $word_id ) {
						$ni = $this->app->utility->array_get( $nis, $word_id, 0 );
						$this->app->db->update( 'word', [
							'count' => $ni,
							'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
						], [
							'id' => $word_id,
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
			$this->app->db->insert( 'document', [
				'post_id' => $post_id,
				'count'   => $dl,
			] );
			$document_id = $this->app->db->get_insert_id();
			$max         = 0;
			if ( count( $data ) > 0 ) {
				$max = max( $data );
			}
			$this->app->db->bulk_insert( 'rel_document_word', [
				'document_id',
				'word_id',
				'count',
				'tf',
			], array_map( function ( $word_id, $count ) use ( $document_id, $dl, $max ) {
				return [
					$document_id,
					$word_id,
					$count,
					$this->calc_tf( $count, $dl, $max ),
				];
			}, array_keys( $data ), array_values( $data ) ) );

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
			$words = $this->app->utility->array_combine( $this->app->db->select( 'word', [
				'id' => [ 'in', $word_ids ],
			], [ 'word_id', 'count', 'idf' ] ), 'word_id' );

			foreach ( $word_ids as $word_id ) {
				$ni  = $this->app->utility->array_get( $nis, $word_id, 0 );
				$row = $this->app->utility->array_get( $words, $word_id, false );
				if ( empty( $row ) ) {
					$c   = - 1;
					$idf = - 1;
				} else {
					$c   = $this->app->utility->array_get( $row, 'count' );
					$idf = $this->app->utility->array_get( $row, 'idf' );
				}
				$new_c   = $ni;
				$new_idf = $ni <= 0 ? 0 : round( $this->calc_idf( $N, $ni ), 6 );
				if ( $c != $new_c || $idf != $new_idf ) {
					$this->app->db->update( 'word', [
						'count' => $ni,
						'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
					], [
						'id' => $word_id,
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
	 * @param int $N
	 *
	 * @return array
	 */
	private function get_update_word_ids( $post_types, $N ) {
		$word_ids = [];
		$this->app->db->transaction( function () use ( $post_types, $N, &$word_ids ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$prev_N = $this->app->get_option( 'document_count' );
			if ( $prev_N != $N ) {
				$this->app->option->set( 'document_count', $N );
				$where = [
					'p.post_status' => 'publish',
					'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
				];
				if ( $subquery = $this->control->get_taxonomy_subquery() ) {
					$where['NOT EXISTS'] = $subquery;
				}
				$post_ids = $this->app->utility->array_pluck( $this->app->db->select( [
					[ 'document', 'd' ],
					[
						[ $wpdb->posts, 'p' ],
						'INNER JOIN',
						[
							'd.post_id',
							'=',
							'p.ID',
						],
					],
				], $where, [
					'DISTINCT d.post_id' => 'post_id',
				] ), 'post_id' );
			} else {
				$subquery = $this->app->db->get_select_sql( [ [ $wpdb->postmeta, 'pm' ] ], [
					'pm.post_id'  => [ '=', 'd.post_id', true ],
					'pm.meta_key' => [ '=', $this->app->post->get_meta_key( 'word_updated' ) ],
				], '"X"' );
				$where    = [
					'p.post_status' => 'publish',
					'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
					'NOT EXISTS'    => [ $subquery ],
				];
				if ( $subquery = $this->control->get_taxonomy_subquery() ) {
					$where['NOT EXISTS'][] = $subquery;
				}

				$post_ids = $this->app->utility->array_pluck( $this->app->db->select( [
					[ 'document', 'd' ],
					[
						[ $wpdb->posts, 'p' ],
						'INNER JOIN',
						[
							'd.post_id',
							'=',
							'p.ID',
						],
					],
				], $where, [
					'DISTINCT d.post_id' => 'post_id',
				] ), 'post_id' );
			}

			$word_ids = $this->app->utility->array_pluck( $this->app->db->select( [
				[ 'rel_document_word', 'rw' ],
				[
					[ 'document', 'd' ],
					'INNER JOIN',
					[
						'd.document_id',
						'=',
						'rw.document_id',
					],
				],
				[
					[ $wpdb->posts, 'p' ],
					'INNER JOIN',
					[
						'd.post_id',
						'=',
						'p.ID',
					],
				],
			], [
				'd.post_id'     => [ 'in', $post_ids ],
				'p.post_status' => 'publish',
			], [
				'DISTINCT rw.word_id' => 'word_id',
			] ), 'word_id' );

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
		$this->app->option->delete( 'posts_indexed' );

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
				$ranking         = $this->get_ranking( $post_id, $important_words, $post_types );

				$this->app->db->delete( 'ranking', [
					'post_id' => $post_id,
				] );
				foreach ( $ranking as $item ) {
					$this->app->db->insert( 'ranking', [
						'post_id'      => $post_id,
						'rank_post_id' => $item['post_id'],
						'score'        => $item['score'],
					] );
				}
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
		$words = $this->app->utility->array_combine( $this->app->db->select( 'word', [
			'word' => [ 'in', $words ],
		], [
			'id',
			'word',
		] ), 'word' );
		$ret   = [];
		$this->app->db->transaction( function () use ( $data, $register, $words, &$ret ) {
			foreach ( $data as $word => $count ) {
				$row     = $this->app->utility->array_get( $words, $word );
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
				$this->app->db->insert( 'word', [
					'word' => $word,
				] );
				$word_id = $this->app->db->get_insert_id();
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
			$this->_excluded = $this->app->utility->array_combine( $this->app->db->select( 'exclude_word' ), 'word', 'word' );
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
		/** @var \wpdb $wpdb */
		global $wpdb;
		$where = [
			'p.post_status' => 'publish',
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
		];
		if ( $subquery = $this->control->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}

		return $this->app->utility->array_get( $this->app->db->select_row( [
			[ 'rel_document_word', 'rw' ],
			[
				[ 'document', 'd' ],
				'INNER JOIN',
				[
					'd.document_id',
					'=',
					'rw.document_id',
				],
			],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					'd.post_id',
					'=',
					'p.ID',
				],
			],
		], $where, [
			'DISTINCT d.document_id' => [
				'COUNT',
				'N',
			],
		] ), 'N' );
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	private function calc_nis( $post_types, $word_ids ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$where = [
			'p.post_status' => 'publish',
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
		];
		if ( isset( $word_ids ) ) {
			$where['rw.word_id'] = [ 'in', $word_ids ];
		}
		if ( $subquery = $this->control->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}
		$data = $this->app->db->select( [
			[ 'rel_document_word', 'rw' ],
			[
				[ 'document', 'd' ],
				'INNER JOIN',
				[
					'd.document_id',
					'=',
					'rw.document_id',
				],
			],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					'd.post_id',
					'=',
					'p.ID',
				],
			],
			[
				[ 'word', 'w' ],
				'INNER JOIN',
				[
					'rw.word_id',
					'=',
					'w.word_id',
				],
			],
		], $where, [
			'rw.word_id',
			'DISTINCT d.document_id' => [
				'COUNT',
				'N',
			],
		], null, null, null, [ 'rw.word_id' ] );

		return $this->app->utility->array_combine( $data, 'word_id', 'N' );
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
		/** @var \wpdb $wpdb */
		global $wpdb;
		$where = [
			'p.post_status' => 'publish',
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
			'rw.word_id'    => [ 'in', $word_ids ],
		];
		if ( $subquery = $this->control->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}

		return $this->app->utility->array_pluck( $this->app->db->select( [
			[ 'rel_document_word', 'rw' ],
			[
				[ 'document', 'd' ],
				'INNER JOIN',
				[
					'd.document_id',
					'=',
					'rw.document_id',
				],
			],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					'd.post_id',
					'=',
					'p.ID',
				],
			],
		], $where, [
			'DISTINCT d.post_id' => 'post_id',
		] ), 'post_id' );
	}

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_important_words( $post_id ) {
		return $this->app->db->select( [
			[ 'rel_document_word', 'rw' ],
			[
				[ 'document', 'd' ],
				'INNER JOIN',
				[
					'rw.document_id',
					'=',
					'd.document_id',
				],
			],
			[
				[ 'word', 'w' ],
				'INNER JOIN',
				[
					'rw.word_id',
					'=',
					'w.word_id',
				],
			],
		], [
			'd.post_id' => $post_id,
		], [
			'rw.word_id',
			'rw.tf * w.idf' => 'tfidf',
			'rw.count',
			'rw.tf',
			'w.word',
			'w.idf',
		], $this->control->get_important_words_count(), null, [
			'tfidf' => 'desc',
		] );
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
		/** @var \wpdb $wpdb */
		global $wpdb;
		$where = [
			'p.post_status' => 'publish',
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
		];
		if ( $subquery = $this->control->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}

		return $this->app->utility->array_get( $this->app->db->select_row( [
			[ 'document', 'd' ],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					'd.post_id',
					'=',
					'p.ID',
				],
			],
		], $where, [
			'd.count' => [ 'AVG', 'cnt' ],
		] ), 'cnt' );
	}

	/**
	 * @link https://en.wikipedia.org/wiki/Okapi_BM25
	 *
	 * @param int $post_id
	 * @param array $words
	 * @param array $post_types
	 * @param bool $is_count
	 * @param int|null $count
	 * @param int|null $page
	 *
	 * @return array|int
	 */
	public function get_ranking( $post_id, $words, $post_types, $is_count = false, $count = null, $page = null ) {
		if ( empty( $words ) ) {
			return [];
		}

		$table = [];
		foreach ( $words as $word ) {
			$word_id = $word['word_id'];
			$n       = $word['count'];
			$table[] = "SELECT $word_id AS word_id, $n AS n";
		}
		$table = implode( ' UNION ', $table );
		$table = "($table)";

		! isset( $count ) and $count = $this->control->get_ranking_count();
		if ( isset( $page ) && $page > 1 ) {
			$offset = ( $page - 1 ) * $count;
		} else {
			$offset = null;
		}

		if ( $is_count ) {
			$field    = [
				'DISTINCT d.post_id' => [
					'COUNT',
					'num',
				],
			];
			$order_by = [];
			$group_by = [];
			$count    = 1;
		} else {
			$k1       = $this->apply_filters( 'bm25_k1', $this->get_bm25_k1() );
			$b        = $this->apply_filters( 'bm25_b', $this->get_bm25_b() );
			$avgdl    = $this->apply_filters( 'avg_dl', $this->calc_avg_dl( $post_types ) );
			$field    = [
				"w.idf * t.n * ( rw.tf * ( $k1 + 1 ) ) / ( rw.tf + $k1 * ( 1 - $b + $b * d.count / $avgdl ) )" => [
					'SUM',
					'score',
				],
				'd.post_id',
			];
			$order_by = [
				'score' => 'desc',
			];
			$group_by = [
				'd.post_id',
			];
		}
		$where = [
			'p.post_status' => 'publish',
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
			'd.post_id'     => [ '!=', $post_id ],
		];
		if ( $subquery = $this->control->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$results = $this->app->db->select( [
			[ 'rel_document_word', 'rw' ],
			[
				[ 'document', 'd' ],
				'INNER JOIN',
				[
					'rw.document_id',
					'=',
					'd.document_id',
				],
			],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					'd.post_id',
					'=',
					'p.ID',
				],
			],
			[
				[ 'word', 'w' ],
				'INNER JOIN',
				[
					'rw.word_id',
					'=',
					'w.word_id',
				],
			],
			[
				[ $table, 't' ],
				'INNER JOIN',
				[
					't.word_id',
					'=',
					'rw.word_id',
				],
			],
		], $where, $field, $count, $offset, $order_by, $group_by );

		if ( $is_count ) {
			return $this->app->utility->array_get( $results[0], 'num', 0 );
		}

		return $results;
	}

}
