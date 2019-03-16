<?php
/**
 * WP_Framework_Cache Configs Setting
 *
 * @version 0.0.2
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

return [

	'100' => [
		'Performance' => [
			'10' => [
				'cache_enabled' => [
					'label'   => 'Cache validity',
					'type'    => 'bool',
					'default' => function ( $app ) {
						/** @var \WP_Framework $app */
						return ! $app->utility->definedv( 'WP_DEBUG' ) || $app->utility->definedv( 'WP_FRAMEWORK_FORCE_CACHE' );
					},
				],
			],
		],
	],

];