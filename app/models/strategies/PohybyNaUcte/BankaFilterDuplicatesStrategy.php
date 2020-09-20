<?php

namespace App\Models\Strategies;

use App\Models\Tables\PlatbaTable;
use App\Models\Orm\Platby\PlatbaEntity;

class BankaFilterDuplicatesStrategy{

	/** @var PlatbaTable */
	private $tbl;

	public function __construct(
		PlatbaTable $tbl)
	{
		$this->tbl = $tbl;
	}

	/**
	 * @param PlatbaEntity[] $plas
	 * @return PlatbaEntity[]
	 */
	public function filter(
		$plas)
	{
		$c = collection($plas)->indexBy(function (
			$v)
		{
			return PlatbaEntity::index($v);
		});

		$pMin = $c->min('when');
		$pMax = $c->max('when');

		// 		dd($pMin);
// 		dd($pMax);

		$dbps = $this->tbl->select('vs, from_cu, when, platba')
			->where('DATE(when) BETWEEN ? AND ?', $pMin->when, $pMax->when)
			->fetchAll();

		$indexBy = function (
			$v)
		{
			return $v->vs . '-' . $v->from_cu . '-' . $v->when->format('dmY') . '-' . $v->platba;
		};

		$dbps = collection($dbps)->indexBy($indexBy)->toArray();

		return $c->filter(function (
			$v,
			$k) use (
		$dbps)
		{
			return !array_key_exists($k, $dbps);
		})->toList();
	}
}