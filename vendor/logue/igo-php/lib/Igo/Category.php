<?php

namespace Igo;

class Category {
	public $id;
	public $length;
	public $invoke;
	public $group;

	public function __construct($i, $l, $iv, $g) {
		$this->id = $i;
		$this->length = $l;
		$this->invoke = $iv;
		$this->group = $g;
	}
}
