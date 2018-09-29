# wp-related-post-jp

投稿同士の類似度の計算を行うWordpressのプラグインです。  
関連記事の提供や全文検索を可能にします。

# スクリーンショット
- 設定画面

![設定画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot1.png)

- 処理中

![処理中画面](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot2.png)

- 有効化前
  - 通常の検索の場合「WordPress」と「あああああ」を含まないとヒットしません。

![有効化前](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot3.png)

- 有効化後
  - 検索語からスコアを計算し高い順に表示するため、完全一致しない場合でも検索結果を出すことが可能です。

![有効化後](https://raw.githubusercontent.com/technote-space/wp-related-post-jp/master/screenshot4.png)

# 要件
- PHP 5.4 以上

# 導入手順
1. ZIPダウンロード  
2. wp-content/plugins に展開  
3. 管理画面から有効化  
4. 左メニュー「Related Post」 > 「進捗」から「インデックス処理を有効化」を押下

投稿データのインデックス化が終わるまではキーワード検索に使用されません。  
インデックスの進捗は管理画面から確認できます。

# 特徴
#### ElasticsearchのようなAnalyzer
- char filter  
  - shortcode
    - WordPressのショートコードを展開
  - code
    - プログラムコードを除外
  - html
    - HTMLタグを除外
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

# 主な設定
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

# 関連記事を表示
関連記事を取得するクエリを生成する前に以下のアクションを発行します。
<pre>
do_action( 'related_post-on_related_post' );
</pre>
このアクションが発行された後の一回のみ、WP_Queryはこのプラグインで算出された関連記事を返します。

例えば関連記事用のテンプレートを以下のように呼び出しているテーマの場合、
<pre>
get_template_part('related-list');
</pre>
functions.php 等に記述するコードは以下のようになります。
<pre>
add_action( 'get_template_part_related-list', function () {
	do_action( 'related_post-on_related_post' );
} );
</pre>
このプラグインの functions.php にはあらかじめ [Cocoon](https://wp-cocoon.com/) 用のコードが記述されているため、
Cocoonを使用している方は別途設定は必要ありません。