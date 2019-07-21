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
	'extractor'     => [
		/**
		 * title_content_tags
		 * title_content
		 * content
		 * title
		 */
		'title_content',
	],
	'char_filters'  => [
		'shortcode',
		'code',
		'comment',
		'html',
		'url',
		'reference',
		'kana',
	],
	'tokenizer'     => [
		/**
		 * goo // クレジットの表記が必要 ( https://labs.goo.ne.jp/jp/apiterm/ )
		 * bigram // if setting [use_bigram_tokenizer] = true
		 */
		'bigram',
		'yahoo',
		'igo',
	],
	'token_filters' => [
		'common',
		'wakati' => [ 'yahoo', 'goo', 'igo' ],
		'bigram' => [ 'bigram' ],
		'max',
	],
];
