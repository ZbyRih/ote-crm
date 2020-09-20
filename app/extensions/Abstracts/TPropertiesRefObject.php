<?php

namespace App\Extensions\Abstracts;

trait TPropertiesRefObject{
	use TPropertiesObject {
		__set as protected TPOSet;
	}

	/** @var Object */
	private $ref;

	public function setRef($ref){
		$this->ref = $ref;
		return $this;
	}

	public function __set($name, $value){
		$this->TPOSet($name, $value);

		if($this->ref){
			$this->ref->$name = $value;
		}
	}
}