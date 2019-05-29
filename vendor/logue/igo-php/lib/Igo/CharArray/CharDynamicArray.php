<?php

namespace Igo\CharArray;

use Igo\IntArray\IntDynamicArray;

class CharDynamicArray extends IntDynamicArray implements CharArrayInterface {
	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("S*", fread($this->fp, 2));
		return $data[1];
	}
}
