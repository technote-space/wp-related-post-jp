# wp-related-post-jp

投稿同士の類似度の計算を行うWordpressのプラグインです。  
関連記事の提供や全文検索を可能にします。

# 要件
- PHP 5.4 以上

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
  - wakati (tokenizer: yahoo, igo)
    - 数字のみや記号のみを除外など
  - bigram (tokenizer: bigram)
    - 記号を含むものを除外など
  - max
    - 単語字数制限

#### Okapi BM25 アルゴリズムによる文章類似度計算
https://en.wikipedia.org/wiki/Okapi_BM25  
https://mieruca-ai.com/ai/tf-idf_okapi-bm25/

# 導入手順
1. ZIPダウンロード  
2. wp-content/plugins に展開  
3. 管理画面から有効化  
4. 進捗タブから「インデックス処理を有効化」を押下

投稿データのインデックス化が終わるまではキーワード検索に使用されません。  
インデックスの進捗は管理画面から確認できます。

