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
 * Class Bm25
 * @package Related_Post\Models
 */
class Bm25 implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/** @var Control $control */
	private $control;

	/** @var Analyzer $analyzer */
	private $analyzer;

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
	public function delete( $post_id, $update_word_ids = array(), $init_ranking_flag = true ) {
		$word_ids   = array();
		$post_types = array();
		if ( $this->app->db->transaction( function () use ( $post_id, &$word_ids, &$post_types, $update_word_ids ) {

			$row = $this->app->db->select( 'document', array(
				'post_id' => $post_id,
			), array( 'id', 'post_type' ), 1 );

			if ( ! empty( $row ) ) {
				$document_id = $row['id'];
				$post_type   = $row['post_type'];
				$post_types  = $this->control->get_post_types( $post_type );
				$word_ids    = \Technote\Models\Utility::array_pluck( $this->app->db->select( 'rel_document_word', array(
					'document_id' => $document_id,
				), 'DISTINCT word_id' ), 'word_id' );
				$this->app->db->delete( 'rel_document_word', array(
					'document_id' => $document_id,
				) );
				$this->app->db->delete( 'document', array(
					'post_id' => $post_id,
				) );

				if ( ! empty( $post_types ) ) {
					$N = $this->calc_n( $post_types ) - 1;
					// 更新しない word だけ 情報を更新
					// 更新するものは更新側で情報を更新
					$word_ids = array_diff( $word_ids, $update_word_ids );
					$nis      = $this->calc_nis( $post_types, $word_ids );
					foreach ( $word_ids as $word_id ) {
						$ni = \Technote\Models\Utility::array_get( $nis, $word_id, 0 );
						$this->app->db->update( 'word', array(
							'count' => $ni,
							'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
						), array(
							'id' => $word_id,
						) );
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
		if ( empty( $post_types ) ) {
			return array();
		}

		$data     = $this->parse( $post );
		$word_ids = array_keys( $data );
		! empty( $word_ids ) and $word_ids = array_combine( $word_ids, $word_ids );
		$dl = array_sum( $data );

		if ( $this->app->db->transaction( function () use ( $post_id, $post_type, $post_types, $data, $dl, &$word_ids, $update_word_now ) {
			$this->delete( $post_id, $word_ids, false );
			$this->app->db->insert( 'document', array(
				'post_id'   => $post_id,
				'post_type' => $post_type,
				'count'     => $dl,
			) );
			$document_id = $this->app->db->get_insert_id();
			$max         = 0;
			if ( count( $data ) > 0 ) {
				$max = max( $data );
			}
			$this->app->db->bulk_insert( 'rel_document_word', array(
				'document_id',
				'word_id',
				'count',
				'tf',
			), array_map( function ( $word_id, $count ) use ( $document_id, $dl, $max ) {
				return array(
					$document_id,
					$word_id,
					$count,
					$this->calc_tf( $count, $dl, $max ),
				);
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

		return array( $post_types, $word_ids );
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
			$words = $this->app->db->select( 'word', array(
				'id' => array( 'in', $word_ids ),
			), array( 'word_id', 'count', 'idf' ) );
			$words = array_combine( \Technote\Models\Utility::array_pluck( $words, 'word_id' ), $words );

			foreach ( $word_ids as $word_id ) {
				$ni  = \Technote\Models\Utility::array_get( $nis, $word_id, 0 );
				$row = \Technote\Models\Utility::array_get( $words, $word_id, false );
				if ( empty( $row ) ) {
					$c   = - 1;
					$idf = - 1;
				} else {
					$c   = \Technote\Models\Utility::array_get( $row, 'count' );
					$idf = \Technote\Models\Utility::array_get( $row, 'idf' );
				}
				$new_c   = $ni;
				$new_idf = $ni <= 0 ? 0 : round( $this->calc_idf( $N, $ni ), 6 );
				if ( $c != $new_c || $idf != $new_idf ) {
					$this->app->db->update( 'word', array(
						'count' => $ni,
						'idf'   => $ni <= 0 ? 0 : $this->calc_idf( $N, $ni ),
					), array(
						'id' => $word_id,
					) );
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
		$word_ids = array();
		$this->app->db->transaction( function () use ( $post_types, $N, &$word_ids ) {
			$prev_N = $this->app->get_option( 'document_count' );
			if ( $prev_N != $N ) {
				$this->app->option->set( 'document_count', $N );
				$post_ids = \Technote\Models\Utility::array_pluck( $this->app->db->select( array(
					array( 'document', 'd' ),
				), array(
					'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
				), array(
					'DISTINCT d.post_id' => array( 'AS', 'post_id' )
				) ), 'post_id' );
			} else {
				/** @var \wpdb $wpdb */
				global $wpdb;
				$subquery = $this->app->db->get_select_sql( array( array( $wpdb->postmeta, 'pm' ) ), array(
					'pm.post_id'  => array( '=', 'd.post_id', true ),
					'pm.meta_key' => array( '=', $this->app->post->get_meta_key( 'word_updated' ) ),
				), '"X"' );

				$post_ids = \Technote\Models\Utility::array_pluck( $this->app->db->select( array(
					array( 'document', 'd' ),
				), array(
					'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
					'NOT EXISTS'  => $subquery,
				), array(
					'DISTINCT d.post_id' => array( 'AS', 'post_id' )
				) ), 'post_id' );
			}

			$word_ids = \Technote\Models\Utility::array_pluck( $this->app->db->select( array(
				array( 'rel_document_word', 'w' ),
				array(
					array( 'document', 'd' ),
					'INNER JOIN',
					array(
						'd.document_id',
						'=',
						'w.document_id'
					),
				),
			), array(
				'd.post_id' => array( 'in', $post_ids ),
			), array(
				'DISTINCT w.word_id' => array( 'AS', 'word_id' )
			) ), 'word_id' );

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

				$this->app->db->delete( 'ranking', array(
					'post_id' => $post_id,
				) );
				foreach ( $ranking as $item ) {
					$this->app->db->insert( 'ranking', array(
						'post_id'      => $post_id,
						'rank_post_id' => $item['post_id'],
						'score'        => $item['score'],
					) );
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
		$words = $this->app->db->select( 'word', array(
			'word' => array( 'in', $words ),
		), array(
			'id',
			'word',
		) );
		$words = array_combine( \Technote\Models\Utility::array_pluck( $words, 'word' ), $words );
		$ret   = array();
		$this->app->db->transaction( function () use ( $data, $register, $words, &$ret ) {
			foreach ( $data as $word => $count ) {
				$row     = \Technote\Models\Utility::array_get( $words, $word );
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
		if ( empty( $row ) ) {
			if ( $register ) {
				$this->app->db->insert( 'word', array(
					'word' => $word,
				) );
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
		return \Technote\Models\Utility::array_get( $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'INNER JOIN',
				array(
					'd.document_id',
					'=',
					'w.document_id'
				),
			),
		), array(
			'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
		), array(
			'DISTINCT d.document_id' => array(
				'COUNT',
				'N'
			),
		), 1, null, null ), 'N' );
	}

	/**
	 * @param array $post_types
	 * @param array|null $word_ids
	 *
	 * @return array
	 */
	private function calc_nis( $post_types, $word_ids ) {
		$where = array(
			'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array(
				'in',
				$post_types
			)
		);
		if ( isset( $word_ids ) ) {
			$where['w.word_id'] = array( 'in', $word_ids );
		}
		$data = $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'INNER JOIN',
				array(
					'd.document_id',
					'=',
					'w.document_id'
				),
			),
		), $where, array(
			'w.word_id',
			'DISTINCT d.document_id' => array(
				'COUNT',
				'N'
			),
		), null, null, null, array( 'w.word_id' ) );

		return array_combine( \Technote\Models\Utility::array_pluck( $data, 'word_id' ), \Technote\Models\Utility::array_pluck( $data, 'N' ) );
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
		return \Technote\Models\Utility::array_pluck( $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'INNER JOIN',
				array(
					'd.document_id',
					'=',
					'w.document_id'
				),
			),
		), array(
			'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'w.word_id'   => array( 'in', $word_ids ),
		), array(
			'DISTINCT d.post_id' => array( 'AS', 'post_id' )
		) ), 'post_id' );
	}

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_important_words( $post_id ) {
		return $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'INNER JOIN',
				array(
					'w.document_id',
					'=',
					'd.document_id'
				)
			),
			array(
				array( 'word', 'word' ),
				'INNER JOIN',
				array(
					'w.word_id',
					'=',
					'word.word_id'
				)
			),
		), array(
			'd.post_id' => $post_id,
		), array(
			'w.word_id',
			"w.tf * word.idf" => array( 'AS', 'tfidf' ),
			"w.count",
		), $this->control->get_important_words_count(), null, array(
			'tfidf' => 'desc',
		) );
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
		return \Technote\Models\Utility::array_get( $this->app->db->select( 'document', array(
			'post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
		), array(
			'count' => array( 'AVG', 'cnt' ),
		), 1 ), 'cnt' );
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
			return array();
		}

		$table = array();
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
			$field    = array(
				'DISTINCT d.post_id' => array(
					'COUNT',
					'num'
				)
			);
			$order_by = array();
			$group_by = array();
			$count    = 1;
		} else {
			$k1       = $this->apply_filters( 'bm25_k1', $this->get_bm25_k1() );
			$b        = $this->apply_filters( 'bm25_b', $this->get_bm25_b() );
			$avgdl    = $this->apply_filters( 'avg_dl', $this->calc_avg_dl( $post_types ) );
			$field    = array(
				"word.idf * t.n * ( w.tf * ( $k1 + 1 ) ) / ( w.tf + $k1 * ( 1 - $b + $b * d.count / $avgdl ) )" => array(
					'SUM',
					'score'
				),
				'd.post_id',
			);
			$order_by = array(
				'score' => 'desc',
			);
			$group_by = array(
				'd.post_id'
			);
		}

		$results = $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'INNER JOIN',
				array(
					'w.document_id',
					'=',
					'd.document_id'
				),
			),
			array(
				array( 'word', 'word' ),
				'INNER JOIN',
				array(
					'w.word_id',
					'=',
					'word.word_id'
				),
			),
			array(
				array( $table, 't' ),
				'INNER JOIN',
				array(
					't.word_id',
					'=',
					'w.word_id',
				),
			),
		), array(
			'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'd.post_id'   => array( '!=', $post_id ),
		), $field, $count, $offset, $order_by, $group_by );

		if ( $is_count ) {
			return \Technote\Models\Utility::array_get( $results, 'num', 0 );
		}

		return $results;
	}

}
