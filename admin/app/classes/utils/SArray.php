<?php

class SArray implements ArrayAccess{

	public $data = [];

	public function __construct($data = []){
		$this->data = $data ?: [];
	}

	/* Methods */
	public function offsetExists($offset){
		return array_key_exists($offset, $this->data);
	}

	public function offsetGet($offset){
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value){
		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset){
		unset($this->data[$offset - 1]);
	}

	public function __get($var){
		if(array_key_exists($var, $this->data)){
			return $this->data[$var];
		}
		return null;
	}

	public function __set($var, $value){
		$this->data[$var] = $value;
	}

	public function __isset($var){
		return array_key_exists($var, $this->data);
	}
}