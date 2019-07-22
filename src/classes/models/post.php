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
use WP_Query;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Post
 * @package Related_Post\Classes\Models
 */
class Post implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter {

	use Singleton, Hook, Presenter, Package;

	/** @var Control $control */
	private $control;

	/** @var Bm25 $bm25 */
	private $bm25;

	/** @var bool $is_related_post */
	private $is_related_post = false;

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
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param WP_Query $query
	 */
	private function pre_get_posts( $query ) {
		if ( $this->is_related_post ) {
			$this->is_related_post = false;
			$this->related_post( $query );

			return;
		}

		if ( $query->is_search() ) {
			if ( $this->apply_filters( 'use_keyword_search' ) && $this->get_control()->cache_get( 'is_valid_posts_search' ) ) {
				$search = $query->get( 's' );
				if ( ! empty( $search ) ) {
					$this->keyword_search( $query, $search );

					return;
				}
			}
		}
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	private function transition_post_status( $new_status, $old_status, $post ) {
		if ( ! $this->app->utility->is_autosave() && $this->get_control()->is_valid_posts_index() ) {
			if ( 'publish' === $new_status ) {
				if ( $this->get_control()->is_invalid_post_type( $post->post_type ) || $this->get_control()->is_invalid_category( $post->ID ) || $this->get_control()->is_invalid_post_status( $post->post_status ) ) {
					$this->get_bm25()->delete( $post->ID );
				} else {
					if ( $this->apply_filters( 'index_background_when_update_post' ) ) {
						$this->app->post->delete( $post->ID, 'indexed' );
						$this->get_control()->cache_set( 'posts_indexed', false );
						$this->get_control()->cache_set( 'word_updated', false );
					} else {
						$this->get_bm25()->update( $post );
					}
				}
			} elseif ( 'publish' === $old_status ) {
				$this->get_bm25()->delete( $post->ID );
			} else {
				return;
			}
			delete_site_transient( $this->get_control()->get_total_posts_count_transient_key() );
			delete_site_transient( $this->get_control()->get_update_posts_count_transient_key() );
			$this->get_control()->unlock_process();
		}
	}

	/**
	 * related post
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function on_related_post() {
		$this->is_related_post = true;
	}

	/**
	 * 投稿一覧
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function edit_post_page() {
		if ( ! $this->get_control()->is_valid_posts_index() ) {
			return;
		}

		$post_type   = $this->app->input->request( 'post_type', 'post' );
		$post_status = $this->app->input->request( 'post_status', 'publish' );
		if ( $this->get_control()->is_invalid_post_type( $post_type ) || $this->get_control()->is_invalid_post_status( $post_status ) ) {
			return;
		}

		$this->app->api->add_use_api_name( 'index_result' );
		$this->app->api->add_use_api_name( 'word_on' );
		$this->app->api->add_use_api_name( 'word_off' );
		$this->setup_modal();

		add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
			$columns['wrpj_show_related_post'] = $this->translate( 'Index Detail' );

			return $columns;
		} );

		add_action( "manage_{$post_type}_posts_custom_column", function ( $column_name, $post_id ) {
			if ( 'wrpj_show_related_post' === $column_name ) {
				$post = get_post( $post_id );
				if ( $post && 'publish' === $post->post_status ) {
					if ( $this->get_control()->is_invalid_category( $post_id ) ) {
						return;
					}
					$this->get_view( 'admin/edit_post', [ 'post_id' => $post_id ], true );
				}
			}
		}, 10, 2 );
	}

	/**
	 * @param WP_Query $query
	 * @param string $search
	 */
	private function keyword_search( $query, $search ) {
		if ( ! empty( $query->query['post_type'] ) && $this->get_control()->is_invalid_post_type( $query->query['post_type'] ) ) {
			return;
		}

		$posts_per_page = $query->get( 'posts_per_page' );
		if ( empty( $posts_per_page ) ) {
			$posts_per_page = get_option( 'posts_per_page' );
		}

		$paged = $query->get( 'paged' ); // phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
		list( $ranking, $total_pages ) = $this->get_posts_ranking_from_keyword( $search, $posts_per_page, $paged );
		if ( ! empty( $ranking ) ) {
			$query->set( 's', '' );
			$query->set( 'post__in', array_keys( $ranking ) );
			$query->set( 'orderby', false );
			$query->set( 'paged', '' );
			$posts_results = function ( $posts, $query ) use ( &$posts_results, $ranking, $search, $total_pages, $paged ) {
				/** @var array $posts */
				/** @var WP_Query $query */
				usort( $posts, function ( $post1, $post2 ) use ( $ranking ) {
					/** @var WP_Post $post1 */
					/** @var WP_Post $post2 */
					$ranking1 = $ranking[ $post1->ID ];
					$ranking2 = $ranking[ $post2->ID ];

					return $ranking1 === $ranking2 ? 0 : ( $ranking1 < $ranking2 ? 1 : -1 );
				} );
				$query->set( 's', $search );
				$query->set( 'paged', $paged );
				$query->max_num_pages = $total_pages;
				remove_filter( 'posts_results', $posts_results );

				return $posts;
			};
			add_filter( 'posts_results', $posts_results, 10, 2 );
		}
	}

	/**
	 * @param WP_Query $query
	 */
	private function related_post( $query ) {
		global $post;
		if ( $post ) {
			$related_posts = $this->get_control()->get_related_posts( $post );
			if ( ! empty( $related_posts ) ) {
				$query->set( 'category__in', null );
				$query->set( 'tag__in', null );
				$query->set( 'orderby', null );

				$query->set( 'p', -1 );
				$posts_results = function () use ( &$posts_results, $related_posts ) {
					remove_filter( 'posts_results', $posts_results );

					return $related_posts;
				};
				add_filter( 'posts_results', $posts_results, 10, 2 );
			}
		}
	}

	/**
	 * @param string $search
	 * @param int $posts_per_page
	 * @param int $paged
	 *
	 * @return array
	 */
	private function get_posts_ranking_from_keyword( $search, $posts_per_page, $paged ) {
		$data = $this->get_bm25()->parse_text( $search, false );
		if ( empty( $data ) ) {
			return [ [], 0 ];
		}

		$words       = $this->app->array->map( $data, function ( $v, $k ) {
			return [
				'word_id' => $k,
				'count'   => $v,
			];
		} );
		$post_types  = $this->get_control()->get_valid_post_types();
		$ranking     = [];
		$total       = $this->get_bm25()->get_ranking( 0, $words, $post_types, true, false, true );
		$total_pages = ceil( $total / $posts_per_page );
		foreach ( $this->get_bm25()->get_ranking( 0, $words, $post_types, true, false, false, $posts_per_page, $paged ) as $item ) {
			$ranking[ $item['post_id'] ] = $item['score'];
		}

		return [ $ranking, $total_pages ];
	}

}
