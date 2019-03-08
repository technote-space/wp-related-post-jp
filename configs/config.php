<?php
/**
 * @version 1.3.2
 * @author Technote
 * @since 1.0.0.0
 * @since 1.2.6 Changed: master > develop (update_info_file_url)
 * @since 1.3.0 Changed: ライブラリの更新 (#28)
 * @since 1.3.2 Changed: db version
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

return [

	// main menu title
	'main_menu_title'                => 'WP Related Post JP',

	// db version
	'db_version'                     => '0.0.2',

	// update
	'update_info_file_url'           => 'https://raw.githubusercontent.com/technote-space/wp-related-post-jp/develop/update.json',

	// suppress setting help contents
	'suppress_setting_help_contents' => true,

	// setting page title
	'setting_page_title'             => 'Detail Settings',

	// setting page priority
	'setting_page_priority'          => 100,

	// setting page slug
	'setting_page_slug'              => 'dashboard',

];
