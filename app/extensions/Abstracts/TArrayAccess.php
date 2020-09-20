<?php

namespace App\Extensions\Abstracts;

trait TArrayAccess{

	public function offsetGet($offset){
		return $this->$offset;
	}

	public function offsetExists($offset){
		return property_exists($this, $offset);
	}

	public function offsetUnset($offset){
		unset($this->$offset);
	}

	public function offsetSet($offset, $value){
		$this->$offset = $value;
	}
}