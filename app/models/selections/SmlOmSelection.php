<?php

namespace App\Models\Selections;

use App\Models\Tables\SmlOmTable;
use App\Models\Enums\SmlOMEnums;
use App\Models\Tables\PlatbaTable;

class SmlOmSelection{

	/** @var SmlOmTable */
	private $tbl;

	/** @var PlatbaTable */
	private $tblPlas;

	public function __construct(
		SmlOmTable $tbl,
		PlatbaTable $tblPlas)
	{
		$this->tbl = $tbl;
		$this->tblPlas = $tblPlas;
	}

	public function getYears()
	{
		$smlMin = $this->tbl->select('MIN(YEAR(od)) AS `y`')->fetch();

		$smlMax = $this->tbl->select('MAX(YEAR(do)) AS `y`')
			->where('do != ', SmlOMEnums::INFINITY)
			->fetch();

		$plaMin = $this->tblPlas->select('MIN(YEAR(when)) AS `y`')->fetch();
		$plaMax = $this->tblPlas->select('MAX(YEAR(when)) AS `y`')->fetch();

		$c = collection([
			$smlMax,
			$smlMin,
			$plaMax,
			$plaMin
		]);

		$min = $c->min('y');
		$max = $c->max('y');

		$ret = [];
		for($i = $min['y']; $i <= $max['y']; $i++){
			$ret[] = $i;
		}

		return $ret;
	}
}