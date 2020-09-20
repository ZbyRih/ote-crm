<?php

namespace App\Models\Strategies\ABO;

use App\Models\ABO\GPCItem;
use App\Models\Values\AccountValue;

class GPCItemsUcetFilterStrategy{

	/** @var string */
	private $cu;

	public function __construct(
		$cu)
	{
		$cu = new AccountValue($cu);
		$this->cu = $cu->toDelim();
	}

	public function filter(
		$items)
	{
		return collection($items)->filter(function (
			GPCItem $v)
		{
			return ltrim($v->AccountNumber, '0') == $this->cu;
		})->toArray();
	}
}