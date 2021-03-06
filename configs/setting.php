<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [
	8 => [
		'Search'   => [
			10 => [
				'use_keyword_search' => [
					'label'   => 'Whether to use indexed results for keyword search.',
					'type'    => 'bool',
					'default' => true,
				],
			],
		],
		'Analyzer' => [
			10 => [
				'use_bigram_tokenizer' => [
					'label'   => 'Whether to use bigram tokenizer.',
					'type'    => 'bool',
					'default' => false,
				],
			],
		],
		'Index'    => [
			10 => [
				'target_post_types'                 => [
					'label'   => "Target post types (comma separated)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => 'post',
				],
				'exclude_categories'                => [
					'label'   => "Exclude category slugs (comma separated)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => '',
				],
				'exclude_ids'                       => [
					'label'   => "Exclude post ids (comma separated)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => '',
				],
				'max_index_target_length'           => [
					'label'   => "Max length of target extracted content (set 0 to invalidate)\nIf change this, it's necessary to run posts index process again.",
					'type'    => 'int',
					'default' => 1000,
					'min'     => 0,
				],
				'index_background_when_update_post' => [
					'label'   => 'Whether to index in the background when post updated.',
					'type'    => 'bool',
					'default' => true,
				],
				'index_interval'                    => [
					'label'   => 'Index posts interval (sec)',
					'type'    => 'int',
					'default' => 10,
					'min'     => 1,
				],
				'index_num_at_once'                 => [
					'label'   => 'Number of index posts at once',
					'type'    => 'int',
					'default' => 20,
					'min'     => 1,
				],
				'index_each_interval'               => [
					'label'   => 'Index posts each interval (ms)',
					'type'    => 'int',
					'default' => 250,
					'min'     => 0,
				],
				'update_ranking_num_at_once'        => [
					'label'   => 'Number of update ranking posts at once',
					'type'    => 'int',
					'default' => 1000,
					'min'     => 1,
				],
				'update_ranking_each_interval'      => [
					'label'   => 'Update ranking posts each interval (ms)',
					'type'    => 'int',
					'default' => 10,
					'min'     => 0,
				],
			],
		],
		'Display'  => [
			10 => [
				'ranking_number'           => [
					'label'   => "Max number of ranking for each post\nIf change this, it's necessary to run posts index ranking again.",
					'type'    => 'int',
					'default' => 10,
					'min'     => 1,
					'max'     => 25,
				],
				'ranking_threshold'        => [
					'label'   => "Threshold of related posts, which is used to eliminate posts whose radio of score to the maximum score is lower than this value.\nIf change this, it's necessary to run posts index ranking again.",
					'type'    => 'float',
					'default' => 0,
					'min'     => 0,
					'max'     => 1,
				],
				'search_threshold'         => [
					'label'   => 'Threshold of search posts, which is used to eliminate posts whose radio of score to the maximum score is lower than this value.',
					'type'    => 'float',
					'default' => 0,
					'min'     => 0,
					'max'     => 1,
				],
				'auto_insert_related_post' => [
					'label'   => 'Auto insert related posts (or add <?php wp_related_posts()?> to your single post template)',
					'type'    => 'bool',
					'default' => false,
				],
				'related_posts_title'      => [
					'label'     => 'Related posts title',
					'default'   => 'More from my site',
					'translate' => true,
				],
			],
		],
	],
];
