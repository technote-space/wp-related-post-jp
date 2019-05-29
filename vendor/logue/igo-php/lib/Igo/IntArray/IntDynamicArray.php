<?php

namespace Igo\IntArray;

class IntDynamicArray implements IntArrayInterface {
	protected $start;
	protected $fileName;
	protected $fp;

	public function __construct($fileName, $start) {
		$this->fileName = $fileName;
		$this->start = $start;
		$this->fp = fopen($this->fileName, "rb");
	}
	public function __destruct() {
		fclose($this->fp);
	}

	public function get($idx) {
		fseek($this->fp, $this->start + ($idx * 4));
		$data = unpack("l*", fread($this->fp, 4));
		return $data[1];
	}
}

