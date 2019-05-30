<?php
namespace Igo;

class MakeLattice {
	private $tagger;
	private $nodesAry;
	private $i;
	private $prevs;
	private $empty = true;

	public function __construct($tagger, &$nodesAry) {
		$this->tagger = $tagger;
		$this->nodesAry = &$nodesAry;
	}

	public function set($i) {
		$this->i = $i;
		$this->prevs = $this->nodesAry[$i];
		$this->empty = true;
	}

	public function call($vn) {
		$this->empty = false;

		if ($vn->isSpace) {
			$this->nodesAry[$this->i + $vn->length] = $this->prevs;
		} else {
			$this->nodesAry[$this->i + $vn->length][] = $this->tagger->setMincostNode($vn, $this->prevs);
		}
	}

	public function isEmpty() {
		return $this->empty;
	}
}