# WP Related Post JP

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

投稿同士の類似度の計算を行うWordpressのプラグインです。  
関連記事の提供や全文検索を可能にします。

## スクリーンショット
- 設定画面

![設定画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-1.png)

![設定画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-7.png)

- 処理中

![処理中画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-2.png)

- 有効化前
  - 通常の検索の場合「WordPress」と「あああああ」を含まないとヒットしません。

![有効化前](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-3.png)

- 有効化後
  - 検索語からスコアを計算し高い順に表示するため、完全一致しない場合でも検索結果を出すことが可能です。

![有効化後](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-4.png)

- 関連記事の確認
  - 投稿一覧から選択した記事の関連記事及び類似度のスコアを確認できます。
  
![関連記事](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-5.png)

- 重要語の確認
  - 投稿一覧から選択した記事の重要語を確認できます。
 
![重要語](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot-6.png)

## 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

## 導入手順
1. ZIPダウンロード  
2. wp-content/plugins に展開  
3. 管理画面から有効化  
4. 左メニュー「WP Related Post JP」 > 「進捗」から「インデックス処理を有効化」を押下

投稿データのインデックス化が終わるまではキーワード検索に使用されません。  
インデックスの進捗は管理画面から確認できます。

## 特徴
#### ElasticsearchのようなAnalyzer
- char filter  
  - shortcode
    - WordPressのショートコードを展開
  - code
    - プログラムコードを除外
  - comment
    - HTMLコメントを除外
  - html
    - HTMLタグを除外
  - reference
    - 文字実体参照を除外
  - kana
    - カタカナや英数字を全角半角に統一

- tokenizer  
  - bigram
  - yahoo
  - igo 
- token filter  
  - common
    - 空白文字を除去
  - wakati (tokenizer: yahoo, igo)
    - 数字のみや記号のみを除外など
  - bigram (tokenizer: bigram)
    - 記号を含むものを除外など
  - max
    - 単語字数制限

#### Okapi BM25 アルゴリズムによる文章類似度計算
https://en.wikipedia.org/wiki/Okapi_BM25  
https://mieruca-ai.com/ai/tf-idf_okapi-bm25/

## 主な設定
- キーワード検索でも使用するかどうか
  - 投稿記事同士の類似度計算のためのプラグインですが、計算結果はキーワード検索でも利用することが可能です。  
  - キーワード検索でも有効にするにはこの設定をtrueにします。
- バイグラムトークナイザーを使用するかどうか
  - true：  
  文字列を２文字ずつ取り出して文章を構成する単語として使用します。
  - false： 
    - ヤフーの設定をしている場合：  
    ヤフーの日本語形態素解析サービスを使用して単語を分割します。  
    利用制限があるため検索で使用する場合に制限に引っかかる可能性があります。
    - ヤフーの設定をしていない場合：  
    Igoという形態素解析器を使用します。  
    ローカルで動作させるため利用制限等はありませんが処理速度等はサーバに依存します。

## 関連記事を表示
### テーマの用意している仕組みを利用する場合
関連記事を取得するクエリを生成する前に以下のアクションを発行します。
<pre>
do_action( 'related_post/on_related_post' );
</pre>
このアクションが発行された後の一回のみ、WP_Queryはこのプラグインで算出された関連記事を返します。

例えば関連記事用のテンプレートを以下のように呼び出しているテーマの場合、
<pre>
get_template_part('related-list');
</pre>
functions.php 等に記述するコードは以下のようになります。
<pre>
add_action( 'get_template_part_related-list', function () {
	do_action( 'related_post/on_related_post' );
} );
</pre>
このプラグインの functions.php にはあらかじめ [Cocoon](https://wp-cocoon.com/) 用のコードが記述されているため、
Cocoonを使用している方は別途設定は必要ありません。

### 直接出力する場合
以下のコードを貼り付けると関連記事が表示されます。
```
<?php wp_related_posts()?>
```

## インデックス対象を変更
デフォルトでは記事のタイトルと本文がインデックスの対象になっています。タイトルに重みづけがされています。
<pre>
str_repeat( $post->post_title . ' ', 3 ) . $post->post_content;
</pre>
あらかじめ「本文のみ」「タイトルのみ」「タイトルと本文とタグ」を対象とする設定が別途用意されており、以下のようなプログラムを functions.php などに記述することで変更することが可能です。
<pre>
add_filter( 'related_post/extractor', function () {
	return 'content';            // 本文のみ
//	return 'title';              // タイトルのみ
//	return 'title_content_tags'; // タイトルと本文とタグ
} );
</pre>

さらに以下のようなプログラムを記述することで、カスタムフィールドなどを含め自由に対象を設定することが可能です。
<pre>
add_filter( 'related_post/extractor', function () {
	return false;
} );
add_filter( 'related_post/extractor_result', function ($d, $post) {
	return $post->title . ' ' . get_post_meta($post->ID, 'custom_field_key', true);
} );
</pre>

## 関連記事の表示を変更
`related_post/related_posts_content` をフィルタすることで関連記事の表示を変更することが可能です。  

例：
```
add_filter( 'related_post/related_posts_content', function (
	/** @noinspection PhpUnusedParameterInspection */
	$content, $control, $title, $post, $related_posts
) {
	/** @var \Related_Post\Classes\Models\Control $control */
	/** @var string $title */
	/** @var array $related_posts */
	ob_start();
	?>
    <style>
        .related_posts_content {
            margin: 10px;
            padding: 10px;
            border: #ccc 1px solid;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -ms-transition: all 0.5s ease;
            -o-transition: all 0.5s ease;
            transition: all 0.5s ease;
            background: white;
        }

        .related_posts_content:hover {
            -webkit-box-shadow: #ccc 0 0 16px;
            -moz-box-shadow: #ccc 0 0 16px;
            box-shadow: #ccc 0 0 16px;
            background: #f0ffff;
        }

        .link-item {
            letter-spacing: -1em;
        }

        .link-item .thumbnail {
            display: inline-block;
            width: 20%;
            margin: 0;
            vertical-align: middle;
        }

        .link-item .thumbnail img {
            vertical-align: middle;
        }

        .link-item .title {
            display: inline-block;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            width: 80%;
            padding: 1em;
            margin: 0;
            font-weight: bold;
            letter-spacing: normal;
        }
    </style>
    <div class="related_posts">
        <h3 class="related_posts_title">
			<?php $control->h( $title ); ?>
        </h3>
        <div class="related_posts_wrap">
			<?php foreach ( $related_posts as $related_post ): ?>
				<?php /** @var WP_Post $related_post */ ?>
                <div class="related_posts_content">
					<?php $control->url( get_permalink( $related_post->ID ), <<< EOS
                    <div class="link-item">
                        <div class="thumbnail">
                            {$control->get_thumbnail( $related_post->ID )}
                        </div>
                        <div class="title">
                            {$related_post->post_title}
                        </div>
                    </div>
EOS
						, false, false, [], true, false ); ?>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
	<?php

	$view = ob_get_contents();
	ob_end_clean();

	return $view;
}, 10, 5 );
```

![関連記事の表示を変更](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/.github/images/201904171559.png)

## Author
[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)

## プラグイン作成用フレームワーク
[WP Content Framework](https://github.com/wp-content-framework/core)
