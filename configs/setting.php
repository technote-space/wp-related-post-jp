<?php
/**
 * @version 1.0.2.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return [

	8 => [

		'Search' => [
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

		'Index' => [

			10 => [
				'ranking_number'                    => [
					'label'   => "Max number of ranking for each post\nIf change this, it's necessary to run posts index ranking again.",
					'default' => 10,
					'min'     => 1,
					'max'     => 25,
				],
				'target_post_types'                 => [
					'label'   => "Target post types (comma separated)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => 'post',
				],
				'exclude_categories'                => [
					'label'   => "Exclude category slugs (comma separated)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => '',
				],
				'exclude_threshold_days'            => [
					'label'   => "Exclude threshold days of old post date (set empty to invalidate)\nIf change this, it's necessary to run posts index ranking again.",
					'default' => '',
				],
				'exclude_threshold_days_field'      => [
					'label'   => "Column name of post to use exclude check\nIf change this, it's necessary to run posts index ranking again.",
					'default' => 'post_modified',
				],
				'max_index_target_length'           => [
					'label'   => "Max length of target extracted content (set 0 to invalidate)\nIf change this, it's necessary to run posts index process again.",
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
	],

	9 => [

		'Yahoo' => [

			10 => [
				'yahoo_client_id'      => [
					'label' => 'Yahoo! Client ID',
				],
				'yahoo_secret'         => [
					'label' => 'Yahoo! Secret',
				],
				'yahoo_retry_count'    => [
					'label'   => 'Retry count',
					'type'    => 'int',
					'default' => 3,
					'min'     => 0,
				],
				'yahoo_retry_interval' => [
					'label'   => 'Retry interval',
					'type'    => 'int',
					'default' => 5,
					'min'     => 1,
				],
			],

		],

		// クレジットの表記が必要 ( https://labs.goo.ne.jp/jp/apiterm/ )
		//		'Goo' => array(
		//			10 => array(
		//				'goo_app_id'         => array(
		//					'label' => 'Goo App ID',
		//				),
		//				'goo_retry_count'    => array(
		//					'label'   => 'Retry count',
		//					'type'    => 'int',
		//					'default' => 3,
		//					'min'     => 0,
		//				),
		//				'goo_retry_interval' => array(
		//					'label'   => 'Retry interval',
		//					'type'    => 'int',
		//					'default' => 5,
		//					'min'     => 1,
		//				),
		//			),
		//		),

	],

];