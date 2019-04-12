=== WP Related Post JP ===
Contributors: technote0space
Tags: related posts, recommend, recommendation, tf-idf
Requires at least: 3.9.3
Requires PHP: 5.6
Tested up to: 5.2
Stable tag: 1.3.12
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Related Post JP provides functions to get related posts.

== Description ==

You can get related post based on post title or contents.
[日本語の説明](https://technote.space/wp-related-post-jp "Documentation in Japanese")

This plugin needs PHP5.6 or higher.

== Installation ==

1. Upload the `wp-related-post-jp` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to [WP Related Post JP] >> [Progress], then click [On index posts] button.

== Screenshots ==

1. Settings
2. Progress
3. Before
4. After

== Upgrade Notice ==

= 1.3.9 =
* 評価による足切り機能を追加しました。
* 　（ダッシュボードの除外設定から関連記事と検索それぞれ設定できます）
* パフォーマンス改善に関する様々な修正を行いました [詳細](https://github.com/wp-content-framework/core/issues/138)

= 1.3.8 =
* README に書いてある filter の仕様 は 1/30 の v1.3.0 以降のバージョンから変更されました。
* related_post『-』... ⇒ related_post『/』... のように スラッシュ区切りになります。
* また v1.3.8 からは simplicity2 に対応しているので simplicity2利用者も functions.php への追加は基本的に不要になります。

== Changelog ==

= 1.3.11 =

* First release

