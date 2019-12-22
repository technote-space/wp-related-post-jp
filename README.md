# WP Related Post JP

[![CI Status](https://github.com/technote-space/wp-related-post-jp/workflows/CI/badge.svg)](https://github.com/technote-space/wp-related-post-jp/actions)
[![Build Status](https://travis-ci.com/technote-space/wp-related-post-jp.svg?branch=master)](https://travis-ci.com/technote-space/wp-related-post-jp)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/wp-related-post-jp/badge)](https://www.codefactor.io/repository/github/technote-space/wp-related-post-jp)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

![バナー](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/banner-772x250.png)

投稿同士の類似度の計算を行うWordpressのプラグインです。  
関連記事の提供や全文検索を可能にします。

[最新バージョン](https://github.com/technote-space/wp-related-post-jp/releases/latest/download/release.zip)

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [要件](#%E8%A6%81%E4%BB%B6)
- [スクリーンショット](#%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88)
- [導入手順](#%E5%B0%8E%E5%85%A5%E6%89%8B%E9%A0%86)
- [特徴](#%E7%89%B9%E5%BE%B4)
    - [ElasticsearchのようなAnalyzer](#elasticsearch%E3%81%AE%E3%82%88%E3%81%86%E3%81%AAanalyzer)
    - [Okapi BM25 アルゴリズムによる文章類似度計算](#okapi-bm25-%E3%82%A2%E3%83%AB%E3%82%B4%E3%83%AA%E3%82%BA%E3%83%A0%E3%81%AB%E3%82%88%E3%82%8B%E6%96%87%E7%AB%A0%E9%A1%9E%E4%BC%BC%E5%BA%A6%E8%A8%88%E7%AE%97)
- [主な設定](#%E4%B8%BB%E3%81%AA%E8%A8%AD%E5%AE%9A)
- [関連記事を表示](#%E9%96%A2%E9%80%A3%E8%A8%98%E4%BA%8B%E3%82%92%E8%A1%A8%E7%A4%BA)
  - [テーマの用意している仕組みを利用する場合](#%E3%83%86%E3%83%BC%E3%83%9E%E3%81%AE%E7%94%A8%E6%84%8F%E3%81%97%E3%81%A6%E3%81%84%E3%82%8B%E4%BB%95%E7%B5%84%E3%81%BF%E3%82%92%E5%88%A9%E7%94%A8%E3%81%99%E3%82%8B%E5%A0%B4%E5%90%88)
  - [直接出力する場合](#%E7%9B%B4%E6%8E%A5%E5%87%BA%E5%8A%9B%E3%81%99%E3%82%8B%E5%A0%B4%E5%90%88)
- [インデックス対象を変更](#%E3%82%A4%E3%83%B3%E3%83%87%E3%83%83%E3%82%AF%E3%82%B9%E5%AF%BE%E8%B1%A1%E3%82%92%E5%A4%89%E6%9B%B4)
- [関連記事の表示を変更](#%E9%96%A2%E9%80%A3%E8%A8%98%E4%BA%8B%E3%81%AE%E8%A1%A8%E7%A4%BA%E3%82%92%E5%A4%89%E6%9B%B4)
- [Author](#author)
- [プラグイン作成用フレームワーク](#%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E4%BD%9C%E6%88%90%E7%94%A8%E3%83%95%E3%83%AC%E3%83%BC%E3%83%A0%E3%83%AF%E3%83%BC%E3%82%AF)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

## スクリーンショット
- 設定画面

![設定画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-1.png)

![設定画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-7.png)

- 処理中

![処理中画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-2.png)

- 有効化前
  - 通常の検索の場合「WordPress」と「あああああ」を含まないとヒットしません。

![有効化前](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-3.png)

- 有効化後
  - 検索語からスコアを計算し高い順に表示するため、完全一致しない場合でも検索結果を出すことが可能です。

![有効化後](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-4.png)

- 関連記事の確認
  - 投稿一覧から選択した記事の関連記事及び類似度のスコアを確認できます。
  
![関連記事](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-5.png)

- 重要語の確認
  - 投稿一覧から選択した記事の重要語を確認できます。
 
![重要語](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/images/assets/screenshot-6.png)

## 導入手順
1. 最新版をGitHubからダウンロード  
[release.zip](https://github.com/technote-space/wp-related-post-jp/releases/latest/download/release.zip)
2. 「プラグインのアップロード」からインストール
![install](https://raw.githubusercontent.com/technote-space/screenshots/master/misc/install-wp-plugin.png)
3. プラグインを有効化 
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
このプラグインの functions.php にはあらかじめ [Cocoon](https://wp-cocoon.com/) 及び [Simplicity2](https://wp-simplicity.com/) 用のコードが記述されているため、
それらを使用している方は別途設定は必要ありません。

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
```php
<?php
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
