<?php

namespace Igo;

class WordDicCallbackCaller {
	private $fn;
	private $wd;

	public function __construct($wd, $fn) {
		$this->wd = $wd;
		$this->fn = $fn;
	}

	public function call($start, $offset, $trieId) {
		$end = $this->wd->indices[$trieId + 1];
		for ($i = $this->wd->indices[$trieId]; $i < $end; $i++) {
			$this->fn->call(new ViterbiNode($i, $start, $offset, $this->wd->costs->get($i), $this->wd->leftIds->get($i), $this->wd->rightIds->get($i), false));
		}
	}
}
