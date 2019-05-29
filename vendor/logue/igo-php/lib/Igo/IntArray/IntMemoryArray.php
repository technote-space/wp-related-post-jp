<?php

namespace Igo\IntArray;

class IntMemoryArray implements IntArrayInterface {
	protected $array;

	public function __construct(&$reader, $count) {
		$this->array = $reader->getIntArray($count);
	}

	public function get($idx) {
		return $this->array[$idx];
	}
}
