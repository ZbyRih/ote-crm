<?php

namespace App\Extensions\Utils;

class LazyValue{

	/** @var callable */
	private $valueGetter;

	/** @var mixed */
	private $value;

	/** @var bool */
	private $loaded = false;

	public function __construct(
		$valueGetter)
	{
		$this->valueGetter = $valueGetter;
	}

	public function getValue()
	{
		if($this->loaded){
			return $this->value;
		}

		$this->loaded = true;
		return $this->value = call_user_func($this->valueGetter);
	}
}