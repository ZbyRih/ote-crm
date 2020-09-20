<?php

namespace App\Extensions\Utils;

class LazyItemsList extends LazyValue{

	/** @var callable */
	private $defaultGetter;

	public function __construct(
		$itemsGetter,
		$defaultGetter)
	{
		parent::__construct($itemsGetter);
		$this->defaultGetter = $defaultGetter;
	}

	public function getDefault()
	{
		return call_user_func($this->defaultGetter, $this->getValue());
	}

	public function getValues()
	{
		return array_values($this->getValue());
	}

	public function getKeys()
	{
		return array_keys($this->getValue());
	}
}