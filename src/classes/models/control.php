<?php
/**
 * @version 1.2.3
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.1.3
 * @since 1.2.3 Updated: setup to use api
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Control
 * @package Related_Post\Classes\Models
 */
class Control implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Presenter, \Technote\Traits\Uninstall;

	/** @var Bm25 $bm25 */
	private $bm25;

	/** @var array $valid_post_types */
	private $valid_post_types;

	/** @var array $exclude_categories */
	private $exclude_categories;

	/** @var array $target_taxonomies */
	private $target_taxonomies;

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
	 * @return array
	 */
	private function load_post_types() {
		if ( ! isset( $this->valid_post_types ) ) {
			$raw_target_post_types = $this->apply_filters( 'target_post_types' );
			$target_post_types     = array_unique( array_filter( array_map( 'trim', explode( ',', $raw_target_post_types ) ) ) );
			$target_post_types     = array_combine( $target_post_types, array_fill( 0, count( $target_post_types ), $target_post_types ) );

			$this->valid_post_types = $this->apply_filters( 'load_post_types', $target_post_types, $raw_target_post_types );
		}

		return $this->valid_post_types;
	}

	/**
	 * @return array
	 */
	private function get_valid_post_types() {
		return $this->app->utility->flatten( $this->load_post_types() );
	}

	/**
	 * @param string $post_type
	 *
	 * @return array|false
	 */
	public function get_post_types( $post_type ) {
		$target_post_types = $this->load_post_types();
		if ( ! isset( $target_post_types[ $post_type ] ) ) {
			return false;
		}

		return $target_post_types[ $post_type ];
	}

	/**
	 * @return array
	 */
	private function get_target_taxonomies() {
		if ( ! isset( $this->target_taxonomies ) ) {
			global $wp_taxonomies;
			$post_types        = $this->get_valid_post_types();
			$target_taxonomies = [];
			foreach ( $wp_taxonomies as $taxonomy => $taxonomy_object ) {
				if ( ! empty( array_intersect( $taxonomy_object->object_type, $post_types ) ) ) {
					$is_category = strpos( $taxonomy, 'categor' ) !== false;
					if ( $this->apply_filters( 'is_category_taxonomy', $is_category, $taxonomy, $taxonomy_object ) ) {
						$target_taxonomies[ $taxonomy ] = $taxonomy;
					}
				}
			}
			$this->target_taxonomies = $this->apply_filters( 'get_target_taxonomies', array_values( $target_taxonomies ), $post_types );
		}

		return $this->target_taxonomies;
	}

	/**
	 * @return array
	 */
	public function get_exclude_category() {
		if ( ! isset( $this->exclude_categories ) ) {
			$raw_exclude_categories = $this->apply_filters( 'exclude_categories' );
			$exclude_categories     = [];
			$target_taxonomies      = $this->get_target_taxonomies();
			if ( ! empty( $target_taxonomies ) ) {
				$target_taxonomies  = array_values( $target_taxonomies );
				$exclude_categories = array_filter( array_map( function ( $category ) use ( $target_taxonomies ) {
					$category = trim( $category );
					if ( empty( $category ) ) {
						return false;
					}
					$terms = get_terms( [
						'get'                    => 'all',
						'number'                 => 1,
						'taxonomy'               => $target_taxonomies,
						'update_term_meta_cache' => false,
						'orderby'                => 'none',
						'suppress_filter'        => true,
						'slug'                   => $category,
					] );
					if ( is_wp_error( $terms ) || empty( $terms ) ) {
						return false;
					}
					$term = array_shift( $terms );

					return $term->term_taxonomy_id;
				}, explode( ',', $raw_exclude_categories ) ), function ( $term ) {
					return ! empty( $term );
				} );
			}
			$this->exclude_categories = $this->apply_filters( 'get_exclude_category', $exclude_categories, $raw_exclude_categories, $target_taxonomies );
		}

		return $this->exclude_categories;
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
		return $this->app->get_option( 'is_valid_posts_index' );
	}

	/**
	 * @param string $post_type
	 *
	 * @return bool
	 */
	private function is_invalid_post_type( $post_type ) {
		return ! ( ( $post_types = $this->get_valid_post_types() ) && in_array( $post_type, $post_types ) );
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function is_invalid_category( $post_id ) {
		if ( ( $exclude_category = $this->get_exclude_category() ) && ! empty( $this->target_taxonomies ) ) {
			$terms = wp_get_post_terms( $post_id, $this->target_taxonomies, [ 'fields' => 'tt_ids' ] );
			if ( ! empty( array_intersect( $terms, $exclude_category ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function transition_post_status( $new_status, $old_status, $post ) {
		if ( ! ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) && $this->is_valid_update_post() ) {
			if ( $new_status === 'publish' ) {
				if ( $this->is_invalid_post_type( $post->post_type ) || $this->is_invalid_category( $post->ID ) ) {
					$this->get_bm25()->delete( $post->ID );
				} else {
					if ( $this->apply_filters( 'index_background_when_update_post' ) ) {
						$this->app->post->delete( $post->ID, 'indexed' );
						$this->app->option->delete( 'posts_indexed' );
						$this->app->option->delete( 'word_updated' );
					} else {
						$this->get_bm25()->update( $post );
					}
				}
			} elseif ( $old_status === 'publish' ) {
				$this->get_bm25()->delete( $post->ID );
			} else {
				return;
			}
			delete_site_transient( $this->get_total_posts_count_transient_key() );
			delete_site_transient( $this->get_update_posts_count_transient_key() );
			$this->unlock_process();
		}
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|bool
	 */
	public function get_related_posts( $post_id ) {
		if ( ! $this->app->post->get( 'setup_ranking', $post_id ) ) {
			if ( $this->app->get_option( 'word_updated', false ) ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$this->get_bm25()->update_ranking( $post_id, $this->get_post_types( $post->post_type ), true );
				}
			} else {
				return false;
			}
		}
		if ( $this->app->post->get( 'setup_ranking', $post_id ) ) {
			return array_filter( array_map( function ( $d ) {
				$post_id = $d['rank_post_id'];
				$score   = $d['score'];
				$post    = get_post( $post_id );
				if ( ! $post || $post->post_status !== 'publish' ) {
					return false;
				}
				$post->score = $score;

				return $post;
			}, $this->app->db->select( 'ranking', [
				'post_id' => $post_id,
			], [ 'rank_post_id', 'score' ], null, null, [ 'score' => 'DESC' ] ) ), function ( $p ) {
				return false !== $p;
			} );
		}

		return false;
	}


	/** @var bool $is_related_post */
	private $is_related_post = false;

	/**
	 * related post
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function on_related_post() {
		$this->is_related_post = true;
	}

	/**
	 * @param \WP_Query $query
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function pre_get_posts( $query ) {
		if ( $this->is_related_post ) {
			$this->is_related_post = false;
			$this->related_post( $query );

			return;
		}

		if ( $query->is_search() ) {
			if ( $this->apply_filters( 'use_keyword_search' ) && $this->app->get_option( 'is_valid_posts_search', false ) ) {
				$q = $query->get( 's' );
				if ( ! empty( $q ) ) {
					$this->keyword_search( $query, $q );

					return;
				}
			}
		}
	}

	/**
	 * @param string $q
	 * @param int $posts_per_page
	 * @param int $paged
	 *
	 * @return array
	 */
	private function get_posts_ranking_from_keyword( $q, $posts_per_page, $paged ) {
		$data = $this->get_bm25()->parse_text( $q, false );
		if ( empty( $data ) ) {
			return [ [], 0 ];
		}

		$words       = array_map( function ( $k, $v ) {
			return [
				'word_id' => $k,
				'count'   => $v,
			];
		}, array_keys( $data ), array_values( $data ) );
		$post_types  = $this->get_valid_post_types();
		$ranking     = [];
		$total       = $this->get_bm25()->get_ranking( 0, $words, $post_types, true );
		$total_pages = ceil( $total / $posts_per_page );
		foreach ( $this->get_bm25()->get_ranking( 0, $words, $post_types, false, $posts_per_page, $paged ) as $item ) {
			$ranking[ $item['post_id'] ] = $item['score'];
		}

		return [ $ranking, $total_pages ];
	}

	/**
	 * @param \WP_Query $query
	 * @param string $q
	 */
	private function keyword_search( $query, $q ) {
		if ( ! empty( $query->query['post_type'] ) && $this->is_invalid_post_type( $query->query['post_type'] ) ) {
			return;
		}
		$posts_per_page = $query->get( 'posts_per_page' );
		if ( empty( $posts_per_page ) ) {
			$posts_per_page = get_option( 'posts_per_page' );
		}
		$paged = $query->get( 'paged' );
		list( $ranking, $total_pages ) = $this->get_posts_ranking_from_keyword( $q, $posts_per_page, $paged );
		if ( ! empty( $ranking ) ) {
			$query->set( 's', '' );
			$query->set( 'post__in', array_keys( $ranking ) );
			$query->set( 'orderby', false );
			$query->set( 'paged', '' );
			$posts_results = function ( $posts, $query ) use ( &$posts_results, $ranking, $q, $total_pages, $paged ) {
				/** @var array $posts */
				/** @var \WP_Query $query */
				usort( $posts, function ( $a, $b ) use ( $ranking ) {
					/** @var \WP_Post $a */
					/** @var \WP_Post $b */
					$ra = $ranking[ $a->ID ];
					$rb = $ranking[ $b->ID ];

					return $ra === $rb ? 0 : ( $ra < $rb ) ? 1 : - 1;
				} );
				$query->set( 's', $q );
				$query->set( 'paged', $paged );
				$query->max_num_pages = $total_pages;
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
	 * @param string $content
	 *
	 * @return string
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function the_content( $content ) {
		global $post;
		if ( ! is_object( $post ) || empty( $content ) || $this->is_invalid_post_type( $post->post_type ) || $this->is_invalid_category( $post->ID ) ) {
			return $content;
		}
		if ( ! ( is_single() && $this->apply_filters( 'auto_insert_related_post' ) ) ) {
			return $content;
		}

		$related_posts = $this->get_related_posts( $post->ID );
		if ( empty( $related_posts ) ) {
			return $content;
		}

		return $content . $this->get_view( 'front/related_posts', [
				'title'         => $this->apply_filters( 'related_posts_title' ),
				'post'          => $post,
				'related_posts' => $related_posts,
			] );
	}


	/**
	 * setup index posts
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function setup_index_posts() {
		if ( $this->app->get_option( 'posts_indexed' ) || ! $this->app->get_option( 'is_valid_posts_index' ) || $this->is_process_running() ) {
			return;
		}

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
		$expire = $this->apply_filters( 'posts_index_expire', $seconds, $running, $process, $seconds );
		set_site_transient( $this->get_transient_key(), time() + $expire, $expire );
		$uuid = $this->app->utility->uuid();
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
		delete_site_transient( $this->get_executing_transient_key() );
		delete_site_transient( $this->get_executing_process_transient_key() );
		delete_site_transient( $this->get_transient_key() );
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
			return [ - 1, $process ];
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

		return [ $ret, $process ];
	}

	/**
	 * @return bool
	 */
	private function is_process_running() {
		if ( get_site_transient( $this->get_transient_key() ) || get_site_transient( $this->get_interval_transient_key() ) ) {
			$transient_timeout = '_site_transient_timeout_' . $this->get_transient_key();
			$timeout           = get_site_option( $transient_timeout );
			if ( false === $timeout ) {
				delete_site_transient( $this->get_transient_key() );
			}

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

		if ( $this->get_update_posts( true ) <= 0 ) {
			$uuid = $this->ranking_process( $uuid );

			if ( $uuid != $this->get_executing_uuid() ) {
				return;
			}

			if ( $this->get_setup_ranking_posts( true ) <= 0 ) {
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
		$posts    = $this->get_update_posts( false, $at_once );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $uuid != $this->get_executing_uuid() ) {
					break;
				}
				$uuid = $this->lock_process( true, 'index process' );

				$this->get_bm25()->update( $post, false );

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
			$cache = [];
			foreach ( $this->get_valid_post_types() as $post_type ) {
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
		$posts    = $this->get_setup_ranking_posts( false, $at_once );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( $uuid != $this->get_executing_uuid() ) {
					break;
				}
				$uuid = $this->lock_process( true, 'ranking process' );

				$post_id   = $post->ID;
				$post_type = $post->post_type;
				$this->get_bm25()->update_ranking( $post_id, $this->get_post_types( $post_type ), true );

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
	 * @param $term_taxonomy_ids
	 * @param string $post_table
	 * @param string $post_id_column
	 * @param string $term_relationships_table
	 *
	 * @return false|string
	 */
	public function get_taxonomy_subquery( $term_taxonomy_ids = null, $post_table = 'p', $post_id_column = 'ID', $term_relationships_table = 'tr' ) {
		! isset( $term_taxonomy_ids ) and $term_taxonomy_ids = $this->get_exclude_category();
		if ( empty( $term_taxonomy_ids ) ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		return $this->app->db->get_select_sql( [ [ $wpdb->term_relationships, $term_relationships_table ] ], [
			$term_relationships_table . '.object_id'        => [ '=', $post_table . '.' . $post_id_column, true ],
			$term_relationships_table . '.term_taxonomy_id' => [ 'in', $term_taxonomy_ids ],
		], '"X"' );
	}

	/**
	 * @param bool $is_count
	 * @param int $limit
	 * @param string $key
	 *
	 * @return array|int
	 */
	private function get_update_posts( $is_count, $limit = 1, $key = 'indexed' ) {
		if ( $limit <= 0 ) {
			return $is_count ? 0 : [];
		}

		if ( $is_count ) {
			$limit    = 1;
			$fields   = [ 'DISTINCT p.ID' => [ 'COUNT', 'num' ] ];
			$order_by = null;
			$group_by = null;
			$output   = ARRAY_A;
		} else {
			$fields   = [ '*', 'p.ID' => 'ID' ];
			$order_by = [ 'p.ID' => 'ASC' ];
			$group_by = [ 'p.ID' ];
			$output   = OBJECT;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$post_types = $this->get_valid_post_types();
		$subquery   = $this->app->db->get_select_sql( [ [ $wpdb->postmeta, 'pm2' ] ], [
			'pm2.post_id'  => [ '=', 'pm.post_id', true ],
			'pm2.meta_key' => [ '=', $this->app->post->get_meta_key( $key ) ],
		], '"X"' );
		$where      = [
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
			'p.post_status' => 'publish',
			'NOT EXISTS'    => [ $subquery ],
		];
		if ( $subquery = $this->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'][] = $subquery;
		}

		$results = $this->app->db->select( [
			[ $wpdb->posts, 'p' ],
			[
				[ $wpdb->postmeta, 'pm' ],
				'LEFT JOIN',
				[
					[ 'p.ID', '=', 'pm.post_id' ],
				],
			],
		], $where, $fields, $limit, null, $order_by, $group_by, $output );

		if ( $is_count ) {
			return $this->app->utility->array_get( $results[0], 'num', 0 );
		}

		return $results;
	}

	/**
	 * @param bool $is_count
	 * @param int $limit
	 *
	 * @return array|int
	 */
	private function get_setup_ranking_posts( $is_count = false, $limit = 1 ) {
		return $this->get_update_posts( $is_count, $limit, 'setup_ranking' );
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
		$post_types = $this->get_valid_post_types();
		$where      = [
			'p.post_type'   => count( $post_types ) === 1 ? reset( $post_types ) : [ 'in', $post_types ],
			'p.post_status' => 'publish',
		];
		if ( $subquery = $this->get_taxonomy_subquery() ) {
			$where['NOT EXISTS'] = $subquery;
		}

		$count = $this->app->utility->array_get( $this->app->db->select_row( [
			[ $wpdb->posts, 'p' ],
		], $where, [ 'DISTINCT p.ID' => [ 'COUNT', 'num' ] ] ), 'num' );

		// index, ranking
		$count *= 2;

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
		$update = $this->get_update_posts( true );
		if ( $update > 0 ) {
			$count = $update + $this->get_update_posts( true, 1, '_' );
		} else {
			$count = $update + $this->get_setup_ranking_posts( true );
		}
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
	 * @param string $key
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function changed_option( $key ) {
		if ( in_array( $key, [
			$this->get_filter_prefix() . 'target_post_types',
			$this->get_filter_prefix() . 'ranking_number',
			$this->get_filter_prefix() . 'exclude_categories',
			$this->get_filter_prefix() . 'exclude_threshold_days',
			$this->get_filter_prefix() . 'exclude_threshold_days_field',
		] ) ) {
			$this->init_posts_rankings();
		} elseif ( in_array( $key, [
			$this->get_filter_prefix() . 'max_index_target_length',
		] ) ) {
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
	 * init posts index
	 */
	public function init_posts_rankings() {
		$this->app->option->delete( 'posts_indexed' );
		$this->app->post->delete_all( 'setup_ranking' );
		delete_site_transient( $this->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_update_posts_count_transient_key() );
		$this->unlock_process();
	}

	/**
	 * pre load admin page
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function post_load_admin_page() {
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

	/**
	 * 投稿一覧
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function edit_post_page() {
		$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'post';
		if ( $this->is_invalid_post_type( $post_type ) ) {
			return;
		}

		$this->app->loader->api->add_use_api_name( 'wrpj_index_result' );
		$this->setup_modal();

		add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
			$columns['wrpj_show_related_post'] = $this->app->translate( 'Recommendation' );

			return $columns;
		} );

		add_action( "manage_{$post_type}_posts_custom_column", function ( $column_name, $post_id ) {
			if ( 'wrpj_show_related_post' === $column_name ) {
				if ( ( $post = get_post( $post_id ) ) && 'publish' === $post->post_status ) {
					if ( $this->is_invalid_category( $post_id ) ) {
						return;
					}
					$this->get_view( 'admin/edit_post', [ 'post_id' => $post_id ], true );
				}
			}
		}, 10, 2 );
	}

	/**
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_index_result_response( $post_id ) {
		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return [
				'message' => $this->app->translate( 'Post not found.' ),
				'posts'   => [],
				'words'   => [],
			];
		}

		$indexed       = $this->app->post->get( 'indexed', $post_id );
		$setup_ranking = $this->app->post->get( 'setup_ranking', $post_id );
		$posts         = $this->get_related_posts( $post_id );
		$words         = $this->get_bm25()->get_important_words( $post_id );

		return [
			'message'       => $this->get_view( 'admin/index_result', [
				'post'          => $post,
				'posts'         => $posts,
				'words'         => $words,
				'indexed'       => $indexed,
				'setup_ranking' => $setup_ranking,
			] ),
			'posts'         => $posts,
			'words'         => $words,
			'indexed'       => $indexed,
			'setup_ranking' => $setup_ranking,
		];
	}
}