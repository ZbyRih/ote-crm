<?php

namespace App\Models\Strategies\ABO;

use App\Models\ABO\GPCItem;
use App\Models\ABO\GPCBase;

class GPCItemsStornoFilterStrategy{

	/**
	 * @param GPCItem[] $items
	 */
	public function filter(
		$items)
	{
		$c = collection($items);

		$storna = $c->filter(function (
			GPCItem $v)
		{
			return $v->Code == GPCBase::CODE_DEBET_STRONO || $v->Code == GPCBase::CODE_KREDIT_STORNO;
		})
			->extract('RecordNumber')
			->toArray();

		return $c->filter(function (
			GPCItem $v)
		{
			return $v->Code == GPCBase::CODE_DEBET || $v->Code == GPCBase::CODE_KREDIT;
		})
			->filter(function (
			$v) use (
		$storna)
		{
			return !in_array($v->RecordNumber, $storna);
		})
			->toArray();
	}
}