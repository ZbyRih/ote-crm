<?php

namespace App\Models\Selections;

use App\Models\Tables\SpotrebaTable;
use App\Models\Entities\SpotrebaDTO;
use App\Models\Tables\FakturaTable;

class SpotrebaByOmYearSelection{

	/** @var FakturaTable */
	private $tblFaktury;

	/** @var SpotrebaTable */
	private $tblSpotreba;

	public function __construct(
		FakturaTable $tblFaktury,
		SpotrebaTable $tblSpotreba)
	{
		$this->tblFaktury = $tblFaktury;
		$this->tblSpotreba = $tblSpotreba;
	}

	/**
	 * @param $year
	 * @return SpotrebaDTO[]
	 */
	public function get(
		$year,
		$omId)
	{
		$fs = $this->tblFaktury->select('YEAR(od) AS year, MIN(od) AS od, MAX(do) AS do, SUM(spotreba) AS mwh')
			->where('YEAR(od) BETWEEN ? AND ?', $year - 3, $year)
			->where('om_id', $omId)
			->where('storno', 0)
			->order('od DESC')
			->group('YEAR(do)')
			->limit(3)
			->fetchAll();

		dd($fs);

		$spotr = collection($fs)->map(
			function (
				$v)
			{
				$spotr = new SpotrebaDTO();
				$spotr->from = $v->od;
				$spotr->to = $v->do;
				$spotr->mwh = $v->mwh;
				$spotr->year = $v->year;

				return $spotr;
			})->toArray();

		if(count($spotr) > 2){
			return $spotr;
		}

		$sp = $this->tblSpotreba->select('YEAR(od) AS year, MIN(od) AS od, MAX(do) AS do, SUM(mwh) AS mwh')
			->where('YEAR(od) BETWEEN ? AND ?', $year - 3, $year)
			->where('odber_mist_id', $omId)
			->order('od DESC')
			->group('YEAR(do)')
			->limit(3 - count($spotr))
			->fetchAll();

		$spspotr = collection($sp)->map(
			function (
				$v)
			{
				$spotr = new SpotrebaDTO();
				$spotr->from = $v->od;
				$spotr->to = $v->do;
				$spotr->mwh = $v->mwh;
				$spotr->year = $v->year;

				return $spotr;
			})->toArray();

		return array_merge($spotr, $spspotr);
	}
}