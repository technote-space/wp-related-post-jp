<?php

namespace Igo\ShortArray;

use Igo\IntArray\IntDynamicArray;

class ShortDynamicArray extends IntDynamicArray implements ShortArrayInterface {
	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 2));
		$data = unpack("s*", fread($this->fp, 2));
		return $data[1];
	}
}
