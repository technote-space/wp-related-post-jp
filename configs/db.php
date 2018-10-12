<?php
/**
 * @version 1.0.0.0
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return array(

	'word' => array(
		'columns' => array(
			'word'  => array(
				'type' => 'VARCHAR(24)',
				'null' => false,
			),
			'count' => array(
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			),
			'idf'   => array(
				'type'     => 'DOUBLE',
				'unsigned' => true,
				'null'     => false,
			),
		),
		'index'   => array(
			'key' => array(
				'word' => array( 'word' ),
			),
		),
	),

	'document' => array(
		'columns' => array(
			'post_id'   => array(
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			),
			'post_type' => array(
				'type'    => 'VARCHAR(20)',
				'null'    => false,
				'default' => 'post',
			),
			'count'     => array(
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			),
		),
		'index'   => array(
			'key'    => array(
				'post_type' => array( 'post_type' ),
			),
			'unique' => array(
				'uk_post_id' => array( 'post_id' ),
			),
		),
	),

	'rel_document_word' => array(
		'columns' => array(
			'document_id' => array(
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			),
			'word_id'     => array(
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			),
			'count'       => array(
				'type'     => 'INT(11)',
				'unsigned' => true,
				'null'     => false,
			),
			'tf'          => array(
				'type'     => 'DOUBLE',
				'unsigned' => true,
				'null'     => false,
			),
		),
		'index'   => array(
			'key'    => array(
				'document_id' => array( 'document_id' ),
				'word_id'     => array( 'word_id' ),
			),
			'unique' => array(
				'uk_document_id_word_id' => array( 'document_id', 'word_id' ),
			),
		),
	),

	'ranking' => array(
		'columns' => array(
			'post_id'      => array(
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			),
			'rank_post_id' => array(
				'type'     => 'BIGINT(20)',
				'unsigned' => true,
				'null'     => false,
			),
			'score'        => array(
				'type' => 'DOUBLE',
				'null' => false,
			)
		),
		'index'   => array(
			'key'    => array(
				'post_id'      => array( 'post_id' ),
				'rank_post_id' => array( 'rank_post_id' ),
			),
			'unique' => array(
				'uk_post_id_rank_post_id' => array( 'post_id', 'rank_post_id' ),
			),
		),
	),

);

