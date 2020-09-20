<?php

namespace App\Components;

use App\Extensions\Utils\LazyItemsList;

class YearItems extends LazyItemsList{

	public function __construct(
		$itemsGetter)
	{
		parent::__construct($itemsGetter, function (
			$items)
		{
			return end($items);
		});
	}
}