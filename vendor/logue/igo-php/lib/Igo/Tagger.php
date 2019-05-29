<?php

namespace Igo;

class Tagger {
	private static $BOS_NODES = array();
	public static $REDUCE = false;
	public static $DIC_ENC;
	private $wdc;
	private $unk;
	private $mtx;
	private $enc;
	private $outEnc;
	/**
	 * デフォルトの設定項目
	 */
	public $options = array(
		// 辞書ディレクトリ
		'dict_dir'          => null,
		// 配列として出力
		'return_as_array'   => false,
		// 実行時の使用メモリを調整
		'reduce_mode'       => true,
		// バイトオーダー
		'little_endian'     => true,
		// mbstringの判定優先順位
		'md_detect_order'   => 'ASCII,JIS,UTF-8,EUC-JP,SJIS',
		// 出力エンコード
		'output_encoding'   => 'UTF-8'
	);

	/**
	 * バイナリ辞書を読み込んで、形態素解析器のインスタンスを作成する
	 *
	 * @param options
	 *            設定項目
	 */
	public function __construct($options) {
		$this->options = array_merge($this->options, $options);

		if (empty($this->options['dict_dir']) || !is_dir($this->options['dict_dir'])){
			throw new \Exception('Dictionary directory has not defined or not readable.');
		}

		self::$REDUCE = $this->options['reduce_mode'];
		self::$BOS_NODES[0] = ViterbiNode::makeBOSEOS();
		self::$DIC_ENC = ($this->options['little_endian']) ? 'UTF-16LE' : 'UTF-16BE';

		$this->wdc = new WordDic($this->options['dict_dir']);
		$this->unk = new Unknown($this->options['dict_dir']);
		$this->mtx = new Matrix($this->options['dict_dir']);
	}

	private function getEnc() {
		return $this->options['output_encoding'] !== null ? $this->options['output_encoding'] : $this->enc;
	}
	/**
	 * 形態素解析を行う
	 *
	 * @param text
	 *            解析対象テキスト
	 * @param result
	 *            解析結果の形態素が追加されるリスト
	 * @return 解析結果の形態素リスト. {@code parse(text,result)=result}
	 */
	public function parse($text, $result = null) {
		if ($result == null) {
			$result = array();
		}

		$this->enc = mb_detect_encoding($text, $this->options['md_detect_order']);
		$utf16 = mb_convert_encoding($text, self::$DIC_ENC, $this->enc);
		$source = array_values(unpack("S*", $utf16));

		for ($vn = $this->parseImpl($source); $vn != null; $vn = $vn->prev) {
			$surface = mb_convert_encoding(substr($utf16, $vn->start << 1, $vn->length << 1), $this->getEnc(), self::$DIC_ENC);
			$feature = mb_convert_encoding($this->wdc->wordData($vn->wordId), $this->getEnc(), self::$DIC_ENC);
			if (!$this->options['return_as_array']) {
				$result[] = new Morpheme($surface, $feature, $vn->start);
			} else {
				$result[] = array("surface" => $surface, "feature" => $feature, "start" => $vn->start);
			}
		}

		return $result;
	}

	/**
	 * 分かち書きを行う
	 *
	 * @param text
	 *            分かち書きされるテキスト
	 * @param result
	 *            分かち書き結果の文字列が追加されるリスト
	 * @return 分かち書きされた文字列のリスト.
	 */
	public function wakati($text, $result = null) {
		if ($result === null) {
			$result = array();
		}

		$this->enc = mb_detect_encoding($text, $this->options['md_detect_order']);
		$utf16 = mb_convert_encoding($text, self::$DIC_ENC, $this->enc);
		$source = array_values(unpack("S*", $utf16));

		for ($vn = $this->parseImpl($source); $vn != null; $vn = $vn->prev) {
			$result[] = mb_convert_encoding(substr($utf16, $vn->start << 1, $vn->length << 1), $this->getEnc(), self::$DIC_ENC);
		}

		return $result;
	}

	private function parseImpl($text) {
		$len = count($text);
		$nodesAry[] = self::$BOS_NODES;
		for ($i = 1; $i <= $len; $i++) {
			$nodesAry[] = array();
		}

		$fn = new MakeLattice($this, $nodesAry);
		for ($i = 0; $i < $len; $i++) {
			if (count($nodesAry[$i]) !== 0) {
				$fn->set($i);
				$this->wdc->search($text, $i, $fn); // 単語辞書から形態素を検索
				$this->unk->search($text, $i, $this->wdc, $fn); // 未知語辞書から形態素を検索
				unset($nodesAry[$i]);
			}
		}

		$cur = $this->setMincostNode(ViterbiNode::makeBOSEOS(), $nodesAry[$len])->prev;

		// reverse
		$head = null;
		while ($cur->prev !== null) {
			$tmp = $cur->prev;
			$cur->prev = $head;
			$head = $cur;
			$cur = $tmp;
		}
		return $head;

	}
	public function setMincostNode($vn, $prevs) {
		$f = $vn->prev = $prevs[0];
		$minCost = $f->cost + $this->mtx->linkCost($f->rightId, $vn->leftId);

		for ($i = 1; $i < count($prevs); $i++) {
			$p = $prevs[$i];
			$cost = $p->cost + $this->mtx->linkCost($p->rightId, $vn->leftId);
			if ($cost < $minCost) {
				$minCost = $cost;
				$vn->prev = $p;
			}
		}
		$vn->cost += $minCost;

		return $vn;
	}
}
