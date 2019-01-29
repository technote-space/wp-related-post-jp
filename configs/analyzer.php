<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.2.6 Added: comment char filter
 * @since 1.3.0 Improved: 文字実体参照を削除 (#29)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [

	// extractor
	'extractor'     => [
//		'title_content_tags',
		'title_content',
//		'content',
//		'title',
	],

	// char filter
	'char_filters'  => [
		'shortcode',
		'code',
		'comment',
		'html',
		'reference',
		'kana',
	],

	// tokenizer
	'tokenizer'     => [
		'bigram', // if setting [use_bigram_tokenizer] = true

		'yahoo',
//		// 'goo', // クレジットの表記が必要 ( https://labs.goo.ne.jp/jp/apiterm/ )
		'igo',
	],

	// token filter
	'token_filters' => [
		'common',
		'wakati' => [ 'yahoo', 'goo', 'igo' ],
		'bigram' => [ 'bigram' ],
		'max',
	],

];
