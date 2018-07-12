<?php
/**
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return array(

	8 => array(

		'Query' => array(
			10 => array(
				'use_keyword_search' => array(
					'label'   => 'Whether to use indexed results for keyword search.',
					'type'    => 'bool',
					'default' => false,
				),
			),
		),

		'Analyzer' => array(
			10 => array(
				'use_bigram_tokenizer' => array(
					'label'   => 'Whether to use bigram tokenizer.',
					'type'    => 'bool',
					'default' => true,
				),
			),
		),

		'Index' => array(

			10 => array(
				'ranking_number'                    => array(
					'label'   => 'Max number of ranking for each post.',
					'default' => 10,
					'min'     => 1,
					'max'     => 25,
				),
				'target_post_types'                 => array(
					'label'   => "Target post types (comma separated)\nIf change this, it's necessary to run posts index process again.",
					'default' => 'post',
				),
				'index_background_when_update_post' => array(
					'label'   => 'Whether to index in the background when post updated.',
					'type'    => 'bool',
					'default' => true,
				),
				'index_interval'                    => array(
					'label'   => 'Index posts interval (sec)',
					'type'    => 'int',
					'default' => 10,
					'min'     => 1,
				),
				'index_num_at_once'                 => array(
					'label'   => 'Number of index posts at once',
					'type'    => 'int',
					'default' => 20,
					'min'     => 1,
				),
				'index_each_interval'               => array(
					'label'   => 'Index posts each interval (ms)',
					'type'    => 'int',
					'default' => 250,
					'min'     => 0,
				),
				'is_valid_update_ranking'           => array(
					'label'   => 'Whether to update ranking in the background.',
					'type'    => 'bool',
					'default' => false,
				),
				'update_ranking_num_at_once'        => array(
					'label'   => 'Number of update ranking posts at once',
					'type'    => 'int',
					'default' => 1000,
					'min'     => 1,
				),
				'update_ranking_each_interval'      => array(
					'label'   => 'Update ranking posts each interval (ms)',
					'type'    => 'int',
					'default' => 10,
					'min'     => 0,
				),
			),

		),

	),

	9 => array(

		'Yahoo' => array(

			10 => array(
				'yahoo_client_id'      => array(
					'label' => 'Yahoo! Client ID',
				),
				'yahoo_secret'         => array(
					'label' => 'Yahoo! Secret',
				),
				'yahoo_retry_count'    => array(
					'label'   => 'Retry count',
					'type'    => 'int',
					'default' => 3,
					'min'     => 0,
				),
				'yahoo_retry_interval' => array(
					'label'   => 'Retry interval',
					'type'    => 'int',
					'default' => 5,
					'min'     => 1,
				),
			),

		),

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

	),

);