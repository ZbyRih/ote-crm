<?php

namespace App\Models\Strategies\Loaders;

use App\Models\Core\DateRange;
use App\Models\Orm\SmlOMs\SmlOMEntity;
use App\Models\Selections\ZalohaSelection;
use Cake\Collection\Collection;
use App\Extensions\Utils\Helpers\ArrayHash;

class LoadZalohyByRangeStrategy{

	/** @var ZalohaSelection */
	private $selZalohy;

	public function __construct(
		ZalohaSelection $selZalohy)
	{
		$this->selZalohy = $selZalohy;
	}

	/**
	 * @param int $klientId
	 * @param DateRange $range
	 * @param SmlOMEntity[] $smls
	 * @return Collection
	 */
	public function load(
		$klientId,
		DateRange $range,
		$smls)
	{
		$zals = collection([]);

		foreach($smls as $s){
			$sRange = new DateRange($s->od, $s->do);
			if(!$lookUpRange = $range->intersection($sRange)){
				continue;
			}

			if(!$zs = $this->selZalohy->getByOmIdAndRange($s->odberMistId, $klientId, $lookUpRange)){
				continue;
			}

			$zals = $zals->append($zs);
		}

		$zals = $zals->map(
			function (
				$v,
				$k)
			{
				$v = ArrayHash::from($v->toArray(), false);
				$v->index = $v->od->format('U') . '-' . $v->odber_mist_id;
				return $v;
			})
			->indexBy('index')
			->sortBy('index', SORT_ASC);

		return $zals;
	}
}