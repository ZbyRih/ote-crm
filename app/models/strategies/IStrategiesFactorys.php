<?php

namespace App\Models\Strategies;

use App\Models\Strategies\Fakturace\ZalohyDoFakturyStrategy;
use App\Models\Strategies\Loaders\LoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\Loaders\LoadZalohyByRangeStrategy;
use App\Models\Strategies\Zalohy\CreateRozpisZalohEntityStrategy;
use App\Models\Strategies\Zalohy\ZalohyDoRozpisuZalohStrategy;

interface ILoadZalohyByRangeStrategy{

	/**
	 * @return LoadZalohyByRangeStrategy
	 */
	public function create();
}

interface ILoadPlatbyZalohByRangeStrategy{

	/**
	 * @return LoadPlatbyZalohByRangeStrategy
	 */
	public function create();
}

interface ICreateRozpisZalohEntityStrategy{

	/**
	 * @return CreateRozpisZalohEntityStrategy
	 */
	public function create();
}

interface IZalohyDoFakturyStrategy{

	/**
	 * @return ZalohyDoFakturyStrategy
	 */
	public function create();
}

interface IZalohyDoRozpisuZalohStrategy{

	/**
	 * @return ZalohyDoRozpisuZalohStrategy
	 */
	public function create();
}