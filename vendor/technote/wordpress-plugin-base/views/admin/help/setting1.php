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
        title と view を指定します。
        functions.php に以下のようなコードを追加します。
        <pre>
add_filter( 'sample_plugin-get_help_contents', function ( $contents, $slug ) {
	if ( 'setting' === $slug ) {
		return array(
			array(
				'title' => 'タブ１',
				'view'  => 'tab1',
			),
			array(
				'title' => 'タブ２',
				'view'  => 'tab2',
			),
		);
	}

	return $contents;
}, 10, 2 );</pre>
        「sample_plugin」や 設定値は適宜変更します。
    </li>
    <li>
        <h4>viewファイルの作成</h4>
        設定で指定したviewごとに「views/admin/help」にファイルを作成します。<br>
        上の例では「views/admin/help/tab1.php」と「views/admin/help/tab2.php」を作成します。<br>
        views/admin/help/tab1.phpの例：
        <pre>
<<<?php ?>?>?php
if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
?>
<<<?php ?>?>div>
    Hello World!
<<<?php ?>?>/div></pre>
    </li>
</ol>
