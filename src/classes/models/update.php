<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Common\Traits\Uninstall;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;
use WP_Framework_Db\Classes\Models\Query\Builder;
use WP_Framework_Presenter\Traits\Presenter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Update
 * @package Related_Post\Classes\Models
 */
class Update implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter, \WP_Framework_Common\Interfaces\Uninstall {

	use Singleton, Hook, Presenter, Uninstall, Package;

	/** @var Control $control */
	private $control;

	/** @var Bm25 $bm25 */
	private $bm25;

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
	 * @return Bm25
	 */
	private function get_bm25() {
		if ( ! isset( $this->bm25 ) ) {
			$this->bm25 = Bm25::get_instance( $this->app );
		}

		return $this->bm25;
	}

	/**
	 * setup index posts
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup_index_posts() {
		if ( $this->get_control()->cache_get( 'posts_indexed' ) || ! $this->get_control()->is_valid_posts_index() || $this->is_process_running() ) {
			return;
		}

		if ( $this->app->utility->doing_cron() ) {
			add_action( $this->get_hook_name(), function () {
				$this->index_posts();
			} );
		} else {
			$this->set_event();
		}
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
		set_site_transient( $this->get_control()->get_transient_key(), time() + $expire, $expire );

		$uuid = $this->app->utility->uuid();
		set_site_transient( $this->get_control()->get_executing_transient_key(), $uuid, $expire );

		if ( isset( $process ) ) {
			set_site_transient( $this->get_control()->get_executing_process_transient_key(), $process, $expire );
		} else {
			delete_site_transient( $this->get_control()->get_executing_process_transient_key() );
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

		return get_site_transient( $this->get_control()->get_executing_transient_key() );
	}

	/**
	 * interval lock
	 */
	private function unlock_interval_process() {
		delete_site_transient( $this->get_control()->get_interval_transient_key() );
	}

	/**
	 * @return array
	 */
	public function get_interval_lock_process() {
		$value1    = get_site_transient( $this->get_control()->get_interval_transient_key() );
		$value2    = get_site_transient( $this->get_control()->get_transient_key() );
		$executing = get_site_transient( $this->get_control()->get_executing_transient_key() );
		$process   = get_site_transient( $this->get_control()->get_executing_process_transient_key() );
		if ( $executing ) {
			$value2 = false;
		}

		if ( empty( $value1 ) && empty( $value2 ) ) {
			return [ -1, $process ];
		}

		if ( empty( $value1 ) ) {
			$value = $value2;
		} elseif ( empty( $value2 ) ) {
			$value = $value1;
		} else {
			$value = max( $value1, $value2 );
		}
		$ret = $value - time();
		if ( $ret < 0 ) {
			$ret = 0;
		}

		return [ $ret, $process ];
	}

	/**
	 * @return bool
	 */
	private function is_process_running() {
		if ( get_site_transient( $this->get_control()->get_transient_key() ) || get_site_transient( $this->get_control()->get_interval_transient_key() ) ) {
			$transient_timeout = '_site_transient_timeout_' . $this->get_control()->get_transient_key();
			$timeout           = get_site_option( $transient_timeout );
			if ( false === $timeout ) {
				delete_site_transient( $this->get_control()->get_transient_key() );
			}

			return true;
		}

		return false;
	}

	/**
	 * index posts
	 */
	private function index_posts() {
		if ( $this->get_control()->cache_get( 'posts_indexed' ) || $this->is_process_running() ) {
			return;
		}

		if ( ! $this->get_control()->is_valid_posts_index() ) {
			$this->lock_process( false );

			return;
		}

		$this->app->log( 'start index process' );

		set_time_limit( 0 );
		$uuid = $this->lock_process();

		if ( $this->get_control()->cache_get( 'db_truncate_required' ) ) {
			$this->db_truncate();

			return;
		}

		$uuid = $this->index_process( $uuid );
		if ( $this->get_executing_uuid() !== $uuid ) {
			$this->app->log( 'interrupted index process' );

			return;
		}

		if ( $this->get_update_posts( true ) <= 0 ) {
			$uuid = $this->ranking_process( $uuid );

			if ( $this->get_executing_uuid() !== $uuid ) {
				$this->app->log( 'interrupted index process' );

				return;
			}

			if ( $this->get_setup_ranking_posts( true ) <= 0 ) {
				$this->get_control()->cache_set( 'posts_indexed', true );
				$this->get_control()->cache_set( 'is_valid_posts_search', true );
			}
		}

		$this->get_control()->unlock_process();
		$this->app->log( 'finished index process' );
	}

	/**
	 * db truncate
	 */
	private function db_truncate() {
		$this->get_control()->cache_set( 'db_truncate_required', false );
		$this->app->post->delete_all( 'indexed' );
		$this->app->post->delete_all( 'setup_ranking' );
		$this->app->db->truncate( 'document' );
		$this->app->db->truncate( 'ranking' );
		$this->app->db->truncate( 'rel_document_word' );
		$this->app->db->truncate( 'word' );
		delete_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		$this->get_control()->unlock_process( 1 );
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
				if ( $this->get_executing_uuid() !== $uuid ) {
					break;
				}
				$uuid = $this->lock_process( true, 'index process' );

				$this->get_bm25()->update( $post, false );

				$count = get_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
				if ( false !== $count ) {
					set_site_transient( $this->get_control()->get_update_posts_count_transient_key(), $count - 1 );
				}
				if ( $interval > 0 ) {
					usleep( $interval );
				}
			}
			delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		}

		return $uuid;
	}

	/**
	 * @return bool|string
	 */
	private function update_word() {
		if ( ! $this->get_control()->cache_get( 'word_updated' ) ) {
			$this->get_control()->cache_set( 'word_updated', true );

			$uuid  = $this->lock_process( true, 'word index process' );
			$cache = [];
			foreach ( $this->get_control()->get_valid_post_types() as $post_type ) {
				if ( in_array( $post_type, $cache, true ) ) {
					continue;
				}
				$post_types = $this->get_control()->get_post_types( $post_type );
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
				if ( $this->get_executing_uuid() !== $uuid ) {
					break;
				}
				$uuid = $this->lock_process( true, 'ranking process' );

				$post_id   = $post->ID;
				$post_type = $post->post_type;
				$this->get_bm25()->update_ranking( $post_id, $this->get_control()->get_post_types( $post_type ), true );

				$count = get_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
				if ( false !== $count ) {
					set_site_transient( $this->get_control()->get_update_posts_count_transient_key(), $count - 1 );
				}
				if ( $interval > 0 ) {
					usleep( $interval );
				}
			}
			delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		}

		return $uuid;
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

		$query = $this->get_control()->from_posts()
			->alias_left_join_wp( 'postmeta', 'pm', 'p.ID', 'pm.post_id' )
			->where_not_exists( function ( $query ) use ( $key ) {
				/** @var Builder $query */
				$query->table( $this->get_wp_table( 'postmeta', 'pm2' ) )
					->where_column( 'pm2.post_id', 'pm.post_id' )
					->where( 'pm2.meta_key', $this->app->post->get_meta_key( $key ) )
					->select_raw( '"X"' );
			} );

		if ( $is_count ) {
			return $query->distinct()->count( 'p.ID' );
		}

		return $query->select( [
			'p.ID',
			'p.post_author',
			'p.post_date',
			'p.post_date_gmt',
			'p.post_content',
			'p.post_title',
			'p.post_excerpt',
			'p.post_status',
			'p.post_name',
			'p.post_modified',
			'p.post_modified_gmt',
			'p.post_parent',
			'p.guid',
			'p.post_type',
		] )->set_object_mode()->limit( $limit )->order_by( 'p.ID' )->group_by( 'p.ID' )->get();
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
	 * @return int
	 */
	public function get_total_posts_count() {
		$count = get_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
		if ( false !== $count ) {
			return $count;
		}

		$count = $this->get_control()->from_posts()->distinct()->count( 'p.ID' );

		// index, ranking
		$count *= 2;

		set_site_transient( $this->get_control()->get_total_posts_count_transient_key(), $count, HOUR_IN_SECONDS );

		return $count;
	}

	/**
	 * @return int
	 */
	public function get_update_posts_count() {
		if ( $this->get_control()->cache_get( 'db_truncate_required' ) ) {
			return $this->get_total_posts_count();
		}

		$count = get_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		if ( false !== $count ) {
			return $count;
		}

		$update = $this->get_update_posts( true );
		if ( $update > 0 ) {
			$count = $update + $this->get_update_posts( true, 1, '_' );
		} else {
			$count = $update + $this->get_setup_ranking_posts( true );
		}
		set_site_transient( $this->get_control()->get_update_posts_count_transient_key(), $count, HOUR_IN_SECONDS );

		return $count;
	}

	/**
	 * @return bool
	 */
	public function on_posts_index() {
		$this->app->option->set( 'is_valid_posts_index', true );

		return $this->get_control()->is_valid_posts_index();
	}

	/**
	 * @return bool
	 */
	public function off_posts_index() {
		if ( $this->get_control()->cache_get( 'posts_indexed' ) ) {
			return false;
		}
		if ( ! $this->get_control()->is_valid_posts_index() ) {
			return true;
		}

		$this->app->option->set( 'is_valid_posts_index', false );
		$this->get_control()->unlock_process();

		return ! $this->get_control()->is_valid_posts_index();
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param string $key
	 */
	private function changed_option( $key ) {
		if ( in_array( $key, [
			$this->get_filter_prefix() . 'target_post_types',
			$this->get_filter_prefix() . 'ranking_number',
			$this->get_filter_prefix() . 'ranking_threshold',
			$this->get_filter_prefix() . 'search_threshold',
			$this->get_filter_prefix() . 'exclude_categories',
			$this->get_filter_prefix() . 'exclude_ids',
		], true ) ) {
			$this->init_posts_rankings();
		} elseif ( in_array( $key, [
			$this->get_filter_prefix() . 'max_index_target_length',
		], true ) ) {
			$this->init_posts_index();
		}
	}

	/**
	 * init posts index
	 */
	public function init_posts_index() {
		$this->get_control()->cache_set( 'db_truncate_required', true );
		$this->get_control()->cache_set( 'posts_indexed', false );
		$this->get_control()->cache_set( 'is_valid_posts_search', false );
		$this->get_control()->cache_set( 'word_updated', false );
		delete_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		$this->get_control()->unlock_process( 60 );
	}

	/**
	 * init posts index
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function init_posts_rankings() {
		$this->get_control()->cache_set( 'posts_indexed', false );
		$this->app->post->delete_all( 'indexed' );
		$this->app->post->delete_all( 'setup_ranking' );
		delete_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
		$this->get_control()->unlock_process();
	}

	/**
	 * post load admin page
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
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
		$this->app->setting->remove_setting( 'assets_version' );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		$this->clear_event();
		$this->get_control()->unlock_process();
		$this->unlock_interval_process();
		$this->get_control()->cache_set( 'db_truncate_required', false );
		delete_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
		delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
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
				'message' => $this->translate( 'Post not found.' ),
				'posts'   => [],
				'words'   => [],
			];
		}

		$indexed       = $this->app->post->get( 'indexed', $post_id );
		$posts         = $this->get_control()->get_related_posts( $post_id );
		$words         = array_filter( $this->get_bm25()->get_important_words( $post_id ), function ( $data ) {
			return ! $this->get_bm25()->is_excluded( $data['word'] );
		} );
		$setup_ranking = $this->app->post->get( 'setup_ranking', $post_id );

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

	/**
	 * @param int $page
	 * @param int $per_page
	 *
	 * @return array
	 */
	public function get_excluded_words( $page, $per_page ) {
		$offset   = $per_page * ( $page - 1 );
		$rows     = $this->table( 'exclude_word' )
			->limit( $per_page + 1 )
			->offset( $offset )
			->order_by_desc( 'updated_at' )
			->order_by_desc( 'id' )
			->get();
		$has_next = count( $rows ) > $per_page;

		return [ array_slice( $rows, 0, $per_page ), $has_next ];
	}

	/**
	 * @param string $word
	 *
	 * @return bool
	 */
	public function on_exclude_word( $word ) {
		$this->table( 'exclude_word' )->update_or_insert( [
			'word' => $word,
		] );
		$this->init_posts_index();

		return true;
	}

	/**
	 * @param string $word
	 *
	 * @return bool
	 */
	public function off_exclude_word( $word ) {
		$this->table( 'exclude_word' )->where( 'word', $word )->delete();
		$this->init_posts_index();

		return true;
	}

	/**
	 * clear event
	 */
	private function clear_event() {
		wp_clear_scheduled_hook( $this->get_hook_name() );
	}

}
