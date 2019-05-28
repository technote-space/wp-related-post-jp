<?php
/**
 * @version 1.3.16
 * @author Technote
 * @since 1.0.0.0
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Related_Post\Classes\Models\Analyzer\Charfilter;

use Related_Post\Classes\Models\Analyzer\Charfilter;

if ( ! defined( 'WP_RELATED_POST_JP' ) ) {
	exit;
}

/**
 * Class Shortcode
 * @package Related_Post\Classes\Models\Analyzer\Charfilter
 */
class Shortcode extends Charfilter {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public function filter( $text ) {
		return do_shortcode( $text );
	}

}
