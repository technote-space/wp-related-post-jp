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

	// extractor
	'extractor'     => array(
//		'title_content_tags',
		'title_content',
//		'content',
//		'title',
	),

	// char filter
	'char_filters'  => array(
		'shortcode',
		'code',
		'html',
		'kana',
	),

	// tokenizer
	'tokenizer'     => array(
		'bigram', // if setting [use_bigram_tokenizer] = true

		'yahoo',
//		// 'goo', // クレジットの表記が必要 ( https://labs.goo.ne.jp/jp/apiterm/ )
		'igo',
	),

	// token filter
	'token_filters' => array(
		'common',
		'wakati' => array( 'yahoo', 'goo', 'igo' ),
		'bigram' => array( 'bigram' ),
		'max',
	),

);
