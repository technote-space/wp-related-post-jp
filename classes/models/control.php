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
 * Class Control
 * @package Related_Post\Models
 */
class Control implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Uninstall;

	/** @var Bm25 $bm25 */
	private $bm25;

	/** @var array $valid_post_types */
	private $valid_post_types;

	/**
	 * @return int
	 */
	public function get_ranking_count() {
		return $this->apply_filters( 'ranking_number' );
	}

	/**
	 * @return int
	 */
	public function get_important_words_count() {
		return $this->apply_filters( 'important_words_count', 25 );
	}

	/**
	 * @param string|null $raw_target_post_types
	 *
	 * @return array
	 */
	private function get_target_post_types( $raw_target_post_types = null ) {
		! isset( $raw_target_post_types ) and $raw_target_post_types = $this->apply_filters( 'target_post_types' );

		return array_unique( array_filter( array_map( 'trim', explode( ',', $raw_target_post_types ) ) ) );
	}

	/**
	 * @return array
	 */
	private function get_valid_post_types() {
		if ( ! isset( $this->valid_post_types ) ) {
			$raw_target_post_types = $this->apply_filters( 'target_post_types' );
			$target_post_types     = $this->get_target_post_types( $raw_target_post_types );
			$target_post_types     = array_combine( $target_post_types, array_fill( 0, count( $target_post_types ), $target_post_types ) );

			$this->valid_post_types = $this->apply_filters( 'get_valid_post_types', $target_post_types, $raw_target_post_types );
		}

		return $this->valid_post_types;
	}

	/**
	 * @param string $post_type
	 *
	 * @return array|false
	 */
	public function get_post_types( $post_type ) {
		$target_post_types = $this->get_valid_post_types();
		if ( ! isset( $target_post_types[ $post_type ] ) ) {
			return false;
		}

		return $target_post_types[ $post_type ];
	}


	/**
	 * @return Bm25
	 */
	private function get_bm25() {
		if ( ! isset( $this->bm25 ) ) {
			$this->bm25 = Bm25::get_instance( $this->app );
		}

		return $this->bm25;
	}

	/**
	 * @return bool
	 */
	private function is_valid_update_post() {
		return $this->app->get_option( 'posts_indexed' ) || $this->app->get_option( 'is_valid_posts_index' );
	}

	/**
	 * @param int $id
	 * @param \WP_Post $post
	 */
	private function save_post( $id, $post ) {
		if ( $this->is_valid_update_post() ) {
			if ( ! ( defined( 'DOUNG_AUTOSAVE' ) && DOING_AUTOSAVE ) && $post->post_status == 'publish' ) {
				if ( $this->apply_filters( 'index_background_when_update_post' ) ) {
					$this->app->post->delete( $id, 'indexed' );
					$this->app->option->delete( 'posts_indexed' );
					$this->app->option->delete( 'word_updated' );
				} else {
					$this->get_bm25()->update( $post );
				}
				delete_site_transient( $this->get_total_posts_count_transient_key() );
				delete_site_transient( $this->get_update_posts_count_transient_key() );
				$this->unlock_process();
			}
		}
	}

	/**
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	private function transition_post_status( $new_status, $old_status, $post ) {
		if ( $this->is_valid_update_post() ) {
			if ( $old_status == 'publish' && $new_status != 'publish' ) {
				$this->get_bm25()->delete( $post->ID );
				delete_site_transient( $this->get_total_posts_count_transient_key() );
				delete_site_transient( $this->get_update_posts_count_transient_key() );
				$this->unlock_process();
			}
		}
	}

	/**
	 * @param int $id
	 */
	private function delete_post( $id ) {
		if ( $this->is_valid_update_post() ) {
			$this->get_bm25()->delete( $id );
			delete_site_transient( $this->get_total_posts_count_transient_key() );
			delete_site_transient( $this->get_update_posts_count_transient_key() );
			$this->unlock_process();
		}
	}

	/**
	 * @param $post_id
	 *
	 * @return array|bool
	 */
	public function get_related_posts( $post_id ) {
		if ( ! $this->app->post->get( 'setup_ranking', $post_id ) ) {
			if ( $this->app->get_option( 'word_updated', false ) ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$post_types = $this->get_post_types( $post->post_type );
					$this->get_bm25()->update_ranking( $post_id, $post_types, true );
				}
			} else {
				return false;
			}
		}
		if ( $this->app->post->get( 'setup_ranking', $post_id ) ) {
			return array_map( function ( $post_id ) {
				return get_post( $post_id );
			}, \Technote\Models\Utility::array_pluck( $this->app->db->select( 'ranking', array(
				'post_id' => $post_id,
			), 'rank_post_id', null, null, array( 'score' => 'DESC' ) ), 'rank_post_id' ) );
		}

		return false;
	}


	/** @var bool $is_related_post */
	private $is_related_post = false;

	/**
	 * related post
	 */
	private function on_related_post() {
		$this->is_related_post = true;
	}

	/**
	 * @param \WP_Query $query
	 */
	private function pre_get_posts( $query ) {
		if ( $query->is_search() ) {
			if ( $this->apply_filters( 'use_keyword_search' ) && $this->app->get_option( 'is_valid_posts_search', false ) ) {
				$q = $query->get( 's' );
				if ( ! empty( $q ) ) {
					$this->keyword_search( $query, $q );

					return;
				}
			}
		}

		if ( $this->is_related_post ) {
			$this->related_post( $query );
		}
	}

	/**
	 * @param \WP_Query $query
	 * @param string $q
	 */
	private function keyword_search( $query, $q ) {
		$data = $this->get_bm25()->parse_text( $q, false );
		if ( empty( $data ) ) {
			return;
		}

		$words          = array_map( function ( $k, $v ) {
			return array(
				'word_id' => $k,
				'count'   => $v,
			);
		}, array_keys( $data ), array_values( $data ) );
		$posts_per_page = $query->get( 'posts_per_page' );
		if ( empty( $posts_per_page ) ) {
			$posts_per_page = get_option( 'posts_per_page' );
		}
		$post_types = \Technote\Models\Utility::flatten( $this->get_valid_post_types() );
		$ranking    = array();
		foreach ( $this->get_bm25()->get_ranking( 0, $words, $post_types, $posts_per_page ) as $item ) {
			$ranking[ $item['post_id'] ] = $item['score'];
		}
		if ( ! empty( $ranking ) ) {
			$query->set( 's', '' );
			$query->set( 'post__in', array_keys( $ranking ) );
			$query->set( 'orderby', false );
			$posts_results = function ( $posts, $query ) use ( &$posts_results, $ranking, $q ) {
				/** @var array $posts */
				/** @var \WP_Query $query */
				usort( $posts, function ( $a, $b ) use ( $ranking ) {
					/** @var \WP_Post $a */
					/** @var \WP_Post $b */
					$ra = $ranking[ $a->ID ];
					$rb = $ranking[ $b->ID ];

					return $ra == $rb ? 0 : ( $ra < $rb ) ? 1 : - 1;
				} );
				$query->set( 's', $q );
				remove_filter( 'posts_results', $posts_results );

				return $posts;
			};
			add_filter( 'posts_results', $posts_results, 10, 2 );
		}
	}

	/**
	 * @param \WP_Query $query
	 */
	private function related_post( $query ) {
		global $post;
		if ( $post ) {
			$related_posts = $this->get_related_posts( $post->ID );
			if ( ! empty( $related_posts ) ) {
				$query->set( 'category__in', null );
				$query->set( 'tag__in', null );
				$query->set( 'orderby', null );
				$query->set( '', $related_posts );

				$query->set( 'p', - 1 );
				$posts_results = function () use ( &$posts_results, $related_posts ) {
					remove_filter( 'posts_results', $posts_results );

					return $related_posts;
				};
				add_filter( 'posts_results', $posts_results, 10, 2 );
			}
		}
	}


	/**
	 * setup index posts
	 */
	private function setup_index_posts() {
		add_action( $this->get_hook_name(), function () {
			$this->index_posts();
		} );
		$this->set_event();
	}

	/**
	 * @return string
	 */
	private function get_hook_name() {
		return $this->app->define->plugin_name . '-index_posts';
	}

	/**
	 * set event
	 */
	private function set_event() {
		if ( ! wp_next_scheduled( $this->get_hook_name() ) ) {
			if ( $this->is_process_running() ) {
				return;
			}
			wp_schedule_single_event( time(), $this->get_hook_name() );
			spawn_cron( time() );
		}
	}

	/**
	 * clear event
	 */
	private function clear_event() {
		wp_clear_scheduled_hook( $this->get_hook_name() );
	}

	/**
	 * @return string
	 */
	private function get_transient_key() {
		return $this->app->plugin_name . '-control-transient';
	}

	/**
	 * @return string
	 */
	private function get_executing_transient_key() {
		return $this->app->plugin_name . '-control-executing-transient';
	}

	/**
	 * @return string
	 */
	private function get_executing_process_transient_key() {
		return $this->app->plugin_name . '-control-executing-process-transient';
	}

	/**
	 * @return string
	 */
	private function get_interval_transient_key() {
		return $this->app->plugin_name . '-control-interval-transient';
	}

	/**
	 * lock
	 *
	 * @param bool $running
	 * @param string|null $process
	 *
	 * @return string
	 */
	private function lock_process( $running = true, $process = null ) {
		if ( $running ) {
			$seconds = MINUTE_IN_SECONDS * 3;
		} else {
			$seconds = 15;
		}
		$expire = $this->apply_filters( 'posts_index_expire', $seconds );
		set_site_transient( $this->get_transient_key(), time() + $expire, $expire );
		$uuid = \Technote\Models\Utility::uuid();
		set_site_transient( $this->get_executing_transient_key(), $uuid, $expire );
		if ( isset( $process ) ) {
			set_site_transient( $this->get_executing_process_transient_key(), $process, $expire );
		} else {
			delete_site_transient( $this->get_executing_process_transient_key() );
		}

		return $uuid;
	}

	/**
	 * @return string|false
	 */
	private function get_executing_uuid() {
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		return get_site_transient( $this->get_executing_transient_key() );
	}

	/**
	 * unlock
	 */
	private function unlock_process() {
		delete_site_transient( $this->get_transient_key() );
		delete_site_transient( $this->get_executing_transient_key() );
		delete_site_transient( $this->get_executing_process_transient_key() );
	}

	/**
	 * interval lock
	 */
	private function lock_interval_process() {
		set_site_transient( $this->get_interval_transient_key(), time() + $this->apply_filters( 'index_interval' ), $this->apply_filters( 'index_interval' ) );
	}

	/**
	 * interval lock
	 */
	private function unlock_interval_process() {
		delete_site_transient( $this->get_interval_transient_key() );
	}

	/**
	 * @return array
	 */
	public function get_interval_lock_process() {
		$value1    = get_site_transient( $this->get_interval_transient_key() );
		$value2    = get_site_transient( $this->get_transient_key() );
		$executing = get_site_transient( $this->get_executing_transient_key() );
		$process   = get_site_transient( $this->get_executing_process_transient_key() );
		$executing and $value2 = false;
		if ( empty( $value1 ) && empty( $value2 ) ) {
			return array( - 1, $process );
		}
		if ( empty( $value1 ) ) {
			$value = $value2;
		} elseif ( empty( $value2 ) ) {
			$value = $value1;
		} else {
			$value = max( $value1, $value2 );
		}
		$ret = $value - time();
		$ret < 0 and $ret = 0;

		return array( $ret, $process );
	}

	/**
	 * @return bool
	 */
	private function is_process_running() {
		if ( get_site_transient( $this->get_transient_key() ) || get_site_transient( $this->get_interval_transient_key() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * index posts
	 */
	private function index_posts() {
		if ( $this->app->get_option( 'posts_indexed' ) || $this->is_process_running() ) {
			return;
		}

		if ( ! $this->app->get_option( 'is_valid_posts_index' ) ) {
			$this->lock_process( false );

			return;
		}

		set_time_limit( 0 );
		$uuid = $this->lock_process();

		$uuid = $this->index_process( $uuid );

		if ( $uuid != $this->get_executing_uuid() ) {
			return;
		}

		$is_end = empty( $this->get_update_posts( 1 ) );
		if ( $is_end ) {
			$uuid = $this->ranking_process( $uuid );

			if ( $uuid != $this->get_executing_uuid() ) {
				return;
			}

			$is_end = empty( $this->get_setup_ranking_posts( 1 ) );
			if ( $is_end ) {
				$this->app->option->set( 'posts_indexed', true );
				$this->app->option->set( 'is_valid_posts_search', true );
			}
		}

		$this->lock_interval_process();
		$this->unlock_process();
	}

	/**
	 * @param string $uuid
	 *
	 * @return string
	 */
	private function index_process( $uuid ) {
		$at_once  = $this->apply_filters( 'index_num_at_once' );
		$interval = $this->apply_filters( 'index_each_interval' ) * 1000;
		$posts    = $this->get_update_posts( $at_once );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $uuid != $this->get_executing_uuid() ) {
					break;
				}
				$uuid = $this->lock_process( true, 'index process' );

				$this->get_bm25()->update( $post, false, false );

				$count = get_site_transient( $this->get_update_posts_count_transient_key() );
				if ( false !== $count ) {
					set_site_transient( $this->get_update_posts_count_transient_key(), $count - 1 );
				}
				if ( $interval > 0 ) {
					usleep( $interval );
				}
			}
			delete_site_transient( $this->get_update_posts_count_transient_key() );
		}

		return $uuid;
	}

	/**
	 * @return bool|string
	 */
	private function update_word() {
		if ( ! $this->app->get_option( 'word_updated', false ) ) {
			$this->app->option->set( 'word_updated', true );

			$uuid  = $this->lock_process( true, 'word index process' );
			$cache = array();
			foreach ( \Technote\Models\Utility::flatten( $this->get_valid_post_types() ) as $post_type ) {
				if ( in_array( $post_type, $cache ) ) {
					continue;
				}
				$post_types = $this->get_post_types( $post_type );
				$cache      = array_merge( $cache, $post_types );
				$this->get_bm25()->update_word( $post_types, null );
			}

			return $uuid;
		}

		return false;
	}

	/**
	 * @param string $uuid
	 *
	 * @return string
	 */
	private function ranking_process( $uuid ) {
		$tmp = $this->update_word();
		if ( false !== $tmp ) {
			$uuid = $tmp;
		}
		$at_once  = $this->apply_filters( 'update_ranking_num_at_once' );
		$interval = $this->apply_filters( 'update_ranking_each_interval' ) * 1000;
		$posts    = $this->get_setup_ranking_posts( $at_once );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $uuid != $this->get_executing_uuid() ) {
					break;
				}
				$uuid = $this->lock_process( true, 'ranking process' );

				$post_id    = $post['post_id'];
				$post_type  = $post['post_type'];
				$post_types = $this->get_post_types( $post_type );
				$this->get_bm25()->update_ranking( $post_id, $post_types, true );

				$count = get_site_transient( $this->get_update_posts_count_transient_key() );
				if ( false !== $count ) {
					set_site_transient( $this->get_update_posts_count_transient_key(), $count - 1 );
				}
				if ( $interval > 0 ) {
					usleep( $interval );
				}
			}
			delete_site_transient( $this->get_update_posts_count_transient_key() );
		}

		return $uuid;
	}

	/**
	 * @param int $limit
	 *
	 * @return array
	 */
	private function get_update_posts( $limit ) {
		if ( $limit <= 0 ) {
			return array();
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$post_types = \Technote\Models\Utility::flatten( $this->get_valid_post_types() );
		$subquery   = $this->app->db->get_select_sql( array( array( $wpdb->postmeta, 'pm2' ) ), array(
			'pm2.post_id'  => array( '=', 'pm.post_id', true ),
			'pm2.meta_key' => array( '=', $this->app->post->get_meta_key( 'indexed' ) ),
		), '"X"' );

		return $this->app->db->select( array(
			array( $wpdb->posts, 'p' ),
			array(
				array( $wpdb->postmeta, 'pm' ),
				'LEFT JOIN',
				array(
					array( 'p.ID', '=', 'pm.post_id' ),
				)
			),
		), array(
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'p.post_status' => 'publish',
			'NOT EXISTS'    => $subquery
		), array( '*', 'p.ID' => array( 'AS', 'ID' ) ), $limit, null, array( 'p.ID' => 'ASC' ), array(
			'p.ID'
		), OBJECT );
	}

	/**
	 * @param int $limit
	 *
	 * @return array
	 */
	private function get_setup_ranking_posts( $limit ) {
		if ( $limit <= 0 || ! $this->app->get_option( 'is_valid_update_ranking' ) ) {
			return array();
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$post_types = \Technote\Models\Utility::flatten( $this->get_valid_post_types() );
		$subquery   = $this->app->db->get_select_sql( array( array( $wpdb->postmeta, 'pm2' ) ), array(
			'pm2.post_id'  => array( '=', 'pm.post_id', true ),
			'pm2.meta_key' => array( '=', $this->app->post->get_meta_key( 'setup_ranking' ) ),
		), '"X"' );

		return $this->app->db->select( array(
			array( $wpdb->posts, 'p' ),
			array(
				array( $wpdb->postmeta, 'pm' ),
				'LEFT JOIN',
				array(
					array( 'p.ID', '=', 'pm.post_id' ),
				)
			),
		), array(
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'p.post_status' => 'publish',
			'NOT EXISTS'    => $subquery
		), array(
			'p.ID' => array( 'AS', 'post_id' ),
			'p.post_type'
		), $limit, null, null, array( 'p.ID' ) );
	}

	/**
	 * @param array $post_types
	 * @param array $post_ids
	 *
	 * @return array
	 */
	private function get_update_words( $post_types, $post_ids ) {
		return \Technote\Models\Utility::array_pluck( $this->app->db->select( array(
			array( 'rel_document_word', 'w' ),
			array(
				array( 'document', 'd' ),
				'LEFT JOIN',
				array(
					'd.document_id',
					'=',
					'w.document_id'
				),
			),
		), array(
			'd.post_type' => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'd.post_id'   => array( 'in', $post_ids ),
		), array(
			'DISTINCT w.word_id' => array( 'AS', 'word_id' )
		) ), 'word_id' );
	}

	/**
	 * @return string
	 */
	private function get_total_posts_count_transient_key() {
		return $this->app->plugin_name . '-control-total-posts-transient';
	}

	/**
	 * @return string
	 */
	private function get_update_posts_count_transient_key() {
		return $this->app->plugin_name . '-control-update-posts-transient';
	}

	/**
	 * @return int
	 */
	public function get_total_posts_count() {
		$count = get_site_transient( $this->get_total_posts_count_transient_key() );
		if ( false !== $count ) {
			return $count;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$post_types = \Technote\Models\Utility::flatten( $this->get_valid_post_types() );

		$count = \Technote\Models\Utility::array_get( $this->app->db->select( array(
			array( $wpdb->posts, 'p' ),
		), array(
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'p.post_status' => 'publish',
		), array( 'DISTINCT p.ID' => array( 'COUNT', 'num' ) ), 1 ), 'num' );

		if ( $this->app->get_option( 'is_valid_update_ranking' ) ) {
			// index, ranking
			$count *= 2;
		}

		set_site_transient( $this->get_total_posts_count_transient_key(), $count, HOUR_IN_SECONDS );

		return $count;
	}

	/**
	 * @return int
	 */
	public function get_update_posts_count() {
		$count = get_site_transient( $this->get_update_posts_count_transient_key() );
		if ( false !== $count ) {
			return $count;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$post_types = \Technote\Models\Utility::flatten( $this->get_valid_post_types() );
		$subquery   = $this->app->db->get_select_sql( array( array( $wpdb->postmeta, 'pm2' ) ), array(
			'pm2.post_id'  => array( '=', 'pm.post_id', true ),
			'pm2.meta_key' => array( '=', $this->app->post->get_meta_key( 'indexed' ) ),
		), '"X"' );

		$count1 = \Technote\Models\Utility::array_get( $this->app->db->select( array(
			array( $wpdb->posts, 'p' ),
			array(
				array( $wpdb->postmeta, 'pm' ),
				'LEFT JOIN',
				array(
					array( 'p.ID', '=', 'pm.post_id' ),
				)
			),
		), array(
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
			'p.post_status' => 'publish',
			'NOT EXISTS'    => $subquery
		), array( 'DISTINCT p.ID' => array( 'COUNT', 'num' ) ), 1 ), 'num' );

		if ( $this->app->get_option( 'is_valid_update_ranking' ) ) {
			$subquery = $this->app->db->get_select_sql( array( array( $wpdb->postmeta, 'pm2' ) ), array(
				'pm2.post_id'  => array( '=', 'pm.post_id', true ),
				'pm2.meta_key' => array( '=', $this->app->post->get_meta_key( 'setup_ranking' ) ),
			), '"X"' );

			$count2 = \Technote\Models\Utility::array_get( $this->app->db->select( array(
				array( $wpdb->posts, 'p' ),
				array(
					array( $wpdb->postmeta, 'pm' ),
					'LEFT JOIN',
					array(
						array( 'p.ID', '=', 'pm.post_id' ),
					)
				),
			), array(
				'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : array( 'in', $post_types ),
				'p.post_status' => 'publish',
				'NOT EXISTS'    => $subquery
			), array( 'DISTINCT p.ID' => array( 'COUNT', 'num' ) ), 1 ), 'num' );
		} else {
			$count2 = 0;
		}

		$count = $count1 + $count2;

		set_site_transient( $this->get_update_posts_count_transient_key(), $count, HOUR_IN_SECONDS );

		return $count;
	}

	/**
	 * @return bool
	 */
	public function on_posts_index() {
		$this->app->option->set( 'is_valid_posts_index', true );

		return $this->app->get_option( 'is_valid_posts_index' );
	}

	/**
	 * @return bool
	 */
	public function off_posts_index() {
		if ( $this->app->get_option( 'posts_indexed' ) ) {
			return false;
		}
		if ( ! $this->app->get_option( 'is_valid_posts_index' ) ) {
			return true;
		}
		$this->app->option->set( 'is_valid_posts_index', false );
		$this->unlock_process();

		return ! $this->app->get_option( 'is_valid_posts_index' );
	}

	/**
	 * clear ranking
	 */
	public function clear_ranking() {
		$this->app->option->delete( 'posts_indexed' );
		$this->app->option->delete( 'word_updated' );
		$this->app->post->delete_all( 'setup_ranking' );
		$this->app->post->delete_all( 'word_updated' );
		$this->app->db->truncate( 'ranking' );
		delete_site_transient( $this->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_update_posts_count_transient_key() );
		$this->unlock_process();
	}

	/**
	 * @param string $key
	 */
	private function changed_option( $key ) {
		if ( $key === $this->get_filter_prefix() . 'target_post_types' ) {
			$this->init_posts_index();
		}
	}

	/**
	 * init posts index
	 */
	public function init_posts_index() {
		$this->app->option->delete( 'posts_indexed' );
		$this->app->option->delete( 'is_valid_posts_search' );
		$this->app->option->delete( 'word_updated' );
		$this->app->post->delete_all( 'indexed' );
		$this->app->post->delete_all( 'setup_ranking' );
		$this->app->db->truncate( 'post_document' );
		$this->app->db->truncate( 'ranking' );
		$this->app->db->truncate( 'rel_document_word' );
		$this->app->db->truncate( 'word' );
		delete_site_transient( $this->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_update_posts_count_transient_key() );
		$this->unlock_process();
	}

	/**
	 * pre load admin page
	 */
	private function pre_load_admin_page() {
		if ( $this->apply_filters( 'use_bigram_tokenizer' ) ) {
			$this->app->setting->remove_setting( 'yahoo_client_id' );
			$this->app->setting->remove_setting( 'yahoo_secret' );
			$this->app->setting->remove_setting( 'yahoo_retry_count' );
			$this->app->setting->remove_setting( 'yahoo_retry_interval' );
			$this->app->setting->remove_setting( 'goo_app_id' );
			$this->app->setting->remove_setting( 'goo_retry_count' );
			$this->app->setting->remove_setting( 'goo_retry_interval' );
		}
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		$this->clear_event();
		$this->unlock_process();
		$this->unlock_interval_process();
		delete_site_transient( $this->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_update_posts_count_transient_key() );
	}
}
