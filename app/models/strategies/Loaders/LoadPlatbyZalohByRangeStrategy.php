<?php

namespace App\Models\Strategies\Loaders;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Core\DateRange;
use App\Models\Enums\PlatbyEnums;
use App\Models\Selections\PlatbaSelection;
use Cake\Collection\Collection;

class LoadPlatbyZalohByRangeStrategy{

	const TYPE_BY_FS = 'fs';

	const TYPE_BY_OM = 'om';

	/** @var PlatbaSelection */
	private $selPlatby;

	/** @var [] */
	private $loaders;

	public function __construct(

		PlatbaSelection $selPlatby)
	{
		$this->selPlatby = $selPlatby;

		$this->loaders = [
			self::TYPE_BY_OM => 'getByOmIdAndRange',
			self::TYPE_BY_FS => 'getByFakSkupIdAndRange'
		];
	}

	/**
	 * @param int $klientId
	 * @param int $paramId
	 * @param DateRange $range
	 * @param string $type
	 * @return Collection
	 */
	public function load(
		$klientId,
		$paramId,
		DateRange $range,
		$type)
	{
		$fce = $this->loaders[$type];

		$res = collection(call_user_func_array([
			$this->selPlatby,
			$fce
		], [
			$paramId,
			$klientId,
			$range->od,
			$range->do,
			PlatbyEnums::USE_ZALOHA
		]))->map(function (
			$v,
			$k)
		{
			return ArrayHash::from($v->toArray(), false);
		});

		return $res;
	}
}