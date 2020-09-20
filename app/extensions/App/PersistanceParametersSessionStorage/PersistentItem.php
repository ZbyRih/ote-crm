<?php

namespace App\Extensions\App;

class PersistentItem{

	/** @var string */
	private $context;

	/** @var string */
	private $key;

	/** @var PersistentParameterSessionStorage */
	private $store;

	/** @var @reference */
	private $property;

	public function __construct($key, $context, &$property = null, PersistentParameterSessionStorage $store){
		$this->key = $key;
		$this->context = $context;
		$this->property = &$property;
		$this->store = $store;
	}

	public function restore(){
		$value = $this->property;
		if($value === null){
			$value = $this->get();
		}
		return $this->set($value);
	}

	public function get(){
		return $this->store->get($this->key, $this->context);
	}

	public function set($value){
		$this->property = $value;
		return $this->store->set($value, $this->key, $this->context);
	}
}