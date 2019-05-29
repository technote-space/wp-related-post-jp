<?php

namespace Igo\ShortArray;

use Igo\IntArray\IntMemoryArray;

class ShortMemoryArray extends IntMemoryArray implements ShortArrayInterface {
	public function __construct(&$reader, $count) {
		$this->array = $reader->getShortArray($count);
	}

	public function get($idx) {
		return parent::get($idx);
	}
}