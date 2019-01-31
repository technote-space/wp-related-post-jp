<?php
/**
 * @version 1.3.2
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.3.0 Changed: trivial change
 * @since 1.3.2 Added: exclude_word table (#22)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [

	'word' => [
		'columns' => [
			'word'  => [
				'type' => 'VARCHAR(24)',
				'null' => false,
			],
			'count' => [
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			],
			'idf'   => [
				'type'     => 'DOUBLE',
				'unsigned' => true,
				'null'     => false,
			],
		],
		'index'   => [
			'key' => [
				'word' => [ 'word' ],
			],
		],
	],

	'document' => [
		'columns' => [
			'post_id' => [
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			],
			'count'   => [
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			],
		],
		'index'   => [
			'unique' => [
				'uk_post_id' => [ 'post_id' ],
			],
		],
	],

	'rel_document_word' => [
		'columns' => [
			'document_id' => [
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			],
			'word_id'     => [
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			],
			'count'       => [
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			],
			'tf'          => [
				'type'     => 'DOUBLE',
				'unsigned' => true,
				'null'     => false,
			],
		],
		'index'   => [
			'key'    => [
				'document_id' => [ 'document_id' ],
				'word_id'     => [ 'word_id' ],
			],
			'unique' => [
				'uk_document_id_word_id' => [ 'document_id', 'word_id' ],
			],
		],
	],

	'ranking' => [
		'columns' => [
			'post_id'      => [
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			],
			'rank_post_id' => [
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			],
			'score'        => [
				'type' => 'DOUBLE',
				'null' => false,
			],
		],
		'index'   => [
			'key'    => [
				'post_id'      => [ 'post_id' ],
				'rank_post_id' => [ 'rank_post_id' ],
			],
			'unique' => [
				'uk_post_id_rank_post_id' => [ 'post_id', 'rank_post_id' ],
			],
		],
	],

	/**
	 * @since 1.3.2
	 */
	'exclude_word' => [
		'columns' => [
			'word' => [
				'type' => 'VARCHAR(24)',
				'null' => false,
			],
		],
		'index'   => [
			'unique' => [
				'word' => [ 'word' ],
			],
		],
	],

];

