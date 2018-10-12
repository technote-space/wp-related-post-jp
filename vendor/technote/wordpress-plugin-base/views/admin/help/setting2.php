<?php
/**
 * Technote Views Admin Help Setting
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
?>

<ol>
    <li>
        <h4>プラグインのslugを確認</h4>
        プラグインライブラリに指定した名前からslugが決まります。
        <pre>
Technote::get_instance( 'Sample_Plugin', __FILE__ );</pre>
        の場合は「sample_plugin」になります。
    </li>
    <li>
        <h4>ヘルプ表示設定</h4>
        $slug が setting の時に 空を返すようにします。
        functions.php に以下のようなコードを追加します。
        <pre>
add_filter( 'sample_plugin-get_help_contents', function ( $contents, $slug ) {
	if ( 'setting' === $slug ) {
		return array();
	}

	return $contents;
}, 10, 2 );</pre>
        「sample_plugin」は適宜変更します。
    </li>
</ol>
