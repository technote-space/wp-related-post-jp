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
use WP_Framework_Presenter\Traits\Presenter;
use WP_Post;
use WP_Taxonomy;
use WP_Term;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Control
 * @package Related_Post\Classes\Models
 */
class Control implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter {

	use Singleton, Hook, Presenter, Package;

	/** @var Bm25 $bm25 */
	private $bm25;

	/** @var array $valid_post_types */
	private $valid_post_types;

	/** @var array $exclude_cats */
	private $exclude_cats;

	/** @var array $exclude_post_ids */
	private $exclude_post_ids;

	/** @var array $target_taxonomies */
	private $target_taxonomies;

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param array $tables
	 *
	 * @return array
	 */
	private function allowed_wp_tables( $tables ) {
		$tables[ $this->get_wp_table( 'term_relationships' ) ] = $this->get_wp_table( 'term_relationships' );

		return $tables;
	}

	/**
	 * @return int
	 */
	public function get_ranking_count() {
		return $this->apply_filters( 'ranking_number' );
	}

	/**
	 * @param bool $is_search
	 *
	 * @return float
	 */
	public function get_score_threshold( $is_search ) {
		return $is_search ? $this->apply_filters( 'search_threshold' ) : $this->apply_filters( 'ranking_threshold' );
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
			$raw_post_types = $this->apply_filters( 'target_post_types' );
			$post_types     = array_unique( array_filter( array_map( 'trim', explode( ',', $raw_post_types ) ) ) );
			$post_types     = array_combine( $post_types, array_fill( 0, count( $post_types ), $post_types ) );

			$this->valid_post_types = $this->apply_filters( 'load_post_types', $post_types, $raw_post_types );
		}

		return $this->valid_post_types;
	}

	/**
	 * @return array
	 */
	public function get_valid_post_types() {
		return $this->app->array->flatten( $this->load_post_types() );
	}

	/**
	 * @param string $post_type
	 *
	 * @return array|false
	 */
	public function get_post_types( $post_type ) {
		$post_types = $this->load_post_types();
		if ( ! isset( $post_types[ $post_type ] ) ) {
			return false;
		}

		return $post_types[ $post_type ];
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
				/** @var WP_Taxonomy $taxonomy_object */
				if ( $taxonomy_object->hierarchical && ! empty( array_intersect( $taxonomy_object->object_type, $post_types ) ) ) {
					if ( $this->apply_filters( 'is_category_taxonomy', true, $taxonomy, $taxonomy_object ) ) {
						foreach ( $taxonomy_object->object_type as $post_type ) {
							$target_taxonomies[ $taxonomy ][ $post_type ] = true;
						}
					}
				}
			}
			$this->target_taxonomies = $this->apply_filters( 'get_target_taxonomies', $target_taxonomies, $post_types );
		}

		return $this->target_taxonomies;
	}

	/**
	 * @return array
	 */
	private function get_exclude_category() {
		if ( ! isset( $this->exclude_cats ) ) {
			$raw_exclude_cats  = $this->app->string->explode( $this->apply_filters( 'exclude_categories' ) );
			$exclude_cats      = [];
			$target_taxonomies = $this->get_target_taxonomies();
			if ( ! empty( $target_taxonomies ) ) {
				$exclude_cats = array_filter( array_map( function ( $category ) use ( $target_taxonomies ) {
					$category = trim( $category );
					if ( empty( $category ) ) {
						return false;
					}
					$terms = get_terms( array_keys( $target_taxonomies ), [
						'get'                    => 'all',
						'number'                 => 1,
						'update_term_meta_cache' => false,
						'orderby'                => 'none',
						'suppress_filter'        => true,
						'slug'                   => $category,
					] );
					if ( is_wp_error( $terms ) || empty( $terms ) ) {
						return false;
					}
					$term = array_shift( $terms );

					return $term;
				}, $raw_exclude_cats ) );
			}
			$this->exclude_cats = $this->apply_filters( 'get_exclude_category', $exclude_cats, $raw_exclude_cats, $target_taxonomies );
		}

		return $this->exclude_cats;
	}

	/**
	 * @return array
	 */
	public function get_exclude_category_id() {
		return $this->app->array->pluck( $this->get_exclude_category(), 'term_taxonomy_id' );
	}

	/**
	 * @return array
	 */
	public function get_category_data() {
		$exclude_category_ids = $this->get_exclude_category_id();
		$target_taxonomies    = $this->get_target_taxonomies();
		$terms                = get_terms( array_keys( $target_taxonomies ), [
			'get'                    => 'all',
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
			'suppress_filter'        => true,
		] );

		$data = [];
		foreach ( $terms as $term ) {
			/** @var WP_Term $term */
			$data[ $term->slug ] = [
				'name'       => $term->name,
				'id'         => $term->term_taxonomy_id,
				'taxonomy'   => $term->taxonomy,
				'post_types' => $this->app->array->map( array_keys( $this->app->array->get( $target_taxonomies, $term->taxonomy ) ), function ( $post_type ) {
					$post_type = get_post_type_object( $post_type );

					return [
						'name'  => $post_type->name,
						'label' => $post_type->label,
					];
				} ),
				'excluded'   => in_array( $term->term_taxonomy_id, $exclude_category_ids, true ),
			];
		}

		return $data;
	}

	/**
	 * @return array
	 */
	public function get_exclude_post_ids() {
		if ( ! $this->exclude_post_ids ) {
			$this->exclude_post_ids = $this->app->array->combine( $this->app->string->explode( $this->apply_filters( 'exclude_ids' ) ), null );
		}

		return $this->exclude_post_ids;
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
	public function is_valid_posts_index() {
		return $this->app->get_option( 'is_valid_posts_index' );
	}

	/**
	 * @param string $post_type
	 *
	 * @return bool
	 */
	private function is_valid_post_type( $post_type ) {
		$post_types = $this->get_valid_post_types();

		return $post_types && in_array( $post_type, $post_types, true );
	}

	/**
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_invalid_post_type( $post_type ) {
		return ! $this->is_valid_post_type( $post_type );
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	public function is_invalid_category( $post_id ) {
		$exclude_category = $this->get_exclude_category_id();
		if ( $exclude_category && ! empty( $this->target_taxonomies ) ) {
			$terms = wp_get_post_terms( $post_id, array_keys( $this->target_taxonomies ), [ 'fields' => 'tt_ids' ] );
			if ( is_array( $terms ) && ! empty( array_intersect( $terms, $exclude_category ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $post_status
	 *
	 * @return bool
	 */
	private function is_valid_post_status( $post_status ) {
		return in_array( $post_status, $this->apply_filters( 'target_post_status', [
			'publish',
			'all',
		] ), true );
	}

	/**
	 * @param string $post_status
	 *
	 * @return bool
	 */
	public function is_invalid_post_status( $post_status ) {
		return ! $this->is_valid_post_status( $post_status );
	}

	/**
	 * @param int|WP_Post|null $wp_post
	 *
	 * @return WP_Post[]|false
	 */
	public function get_related_posts( $wp_post = null ) {
		if ( ! $this->is_valid_posts_index() ) {
			return false;
		}
		if ( ! isset( $wp_post ) ) {
			global $post;
			$wp_post = $post;
		} else {
			$wp_post = get_post( $wp_post );
		}

		if ( $this->check_ranking_post( $wp_post ) ) {
			return false;
		}

		if ( ! $this->app->post->get( 'setup_ranking', $wp_post->ID ) ) {
			if ( $this->cache_get( 'word_updated' ) ) {
				$this->get_bm25()->update_ranking( $wp_post->ID, $this->get_post_types( $wp_post->post_type ), true );
			} else {
				return false;
			}
		}

		if ( $this->app->post->get( 'setup_ranking', $wp_post->ID ) ) {
			return $this->filter_ranking( $wp_post );
		}

		return false;
	}

	/**
	 * @param WP_Post $wp_post
	 *
	 * @return bool
	 */
	private function check_ranking_post( $wp_post ) {
		return empty( $wp_post ) || ! $wp_post instanceof WP_Post || $this->is_invalid_post_type( $wp_post->post_type ) || $this->is_invalid_category( $wp_post->ID ) || $this->is_invalid_post_status( $wp_post->post_status );
	}

	/**
	 * @param WP_Post $wp_post
	 *
	 * @return array
	 */
	private function filter_ranking( $wp_post ) {
		return array_filter(
			array_map(
				function ( $data ) {
					$post_id = $data['rank_post_id'];
					$score   = $data['score'];
					$post    = get_post( $post_id );
					if ( ! $post || 'publish' !== $post->post_status ) {
						return false;
					}
					$post->score = $score;

					return $post;
				},
				$this->table( 'ranking' )->where( 'post_id', $wp_post->ID )->select( [ 'rank_post_id', 'score' ] )->order_by_desc( 'score' )->get()
			),
			function ( $post ) {
				return false !== $post;
			}
		);
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	private function the_content( $content ) {
		if ( ! ( is_single() && $this->apply_filters( 'auto_insert_related_post' ) ) ) {
			return $content;
		}

		return $content . $this->get_related_posts_content();
	}

	/**
	 * @param null|WP_Post $wp_post
	 *
	 * @return string
	 */
	public function get_related_posts_content( $wp_post = null ) {
		$related_posts = $this->get_related_posts( $wp_post );
		if ( empty( $related_posts ) ) {
			return '';
		}

		$title = $this->apply_filters( 'related_posts_title' );

		return $this->apply_filters( 'related_posts_content', $this->get_view( 'front/related_posts', [
			'title'         => $title,
			'post'          => $wp_post,
			'related_posts' => $related_posts,
		] ), $this, $title, $wp_post, $related_posts );
	}

	/**
	 * @return string
	 */
	public function get_total_posts_count_transient_key() {
		return $this->app->plugin_name . '-control-total-posts-transient';
	}

	/**
	 * @return string
	 */
	public function get_update_posts_count_transient_key() {
		return $this->app->plugin_name . '-control-update-posts-transient';
	}

	/**
	 * @return string
	 */
	public function get_transient_key() {
		return $this->app->plugin_name . '-control-transient';
	}

	/**
	 * @return string
	 */
	public function get_executing_transient_key() {
		return $this->app->plugin_name . '-control-executing-transient';
	}

	/**
	 * @return string
	 */
	public function get_executing_process_transient_key() {
		return $this->app->plugin_name . '-control-executing-process-transient';
	}

	/**
	 * @return string
	 */
	public function get_interval_transient_key() {
		return $this->app->plugin_name . '-control-interval-transient';
	}

	/**
	 * unlock
	 *
	 * @param null|int $interval
	 */
	public function unlock_process( $interval = null ) {
		$this->lock_interval_process( $interval );
		delete_site_transient( $this->get_executing_transient_key() );
		delete_site_transient( $this->get_executing_process_transient_key() );
		delete_site_transient( $this->get_transient_key() );
	}

	/**
	 * interval lock
	 *
	 * @param null|int $interval
	 */
	private function lock_interval_process( $interval = null ) {
		if ( ! isset( $interval ) ) {
			$interval = $this->apply_filters( 'index_interval' );
		}
		set_site_transient( $this->get_interval_transient_key(), time() + $interval, $interval );
	}

}
