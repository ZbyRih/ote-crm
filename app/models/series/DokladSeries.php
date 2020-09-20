<?php

namespace App\Models\Series;

use App\Extensions\Abstracts\NumberSeries;
use App\Extensions\Interfaces\ISeries;
use App\Models\Tables\DokladyTable;

class DokladSeries extends NumberSeries implements ISeries{

	/** @var DokladyTable */
	private $tbl;

	public function __construct(
		DokladyTable $tbl)
	{
		$this->tbl = $tbl;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Abstracts\NumberSeries::next()
	 */
	public function next(
		$year)
	{
		// 		5YYYYNNNNN
		// 		5201600005
		$new = '5' . $year . '00001';

		if(!$last = $this->tbl->select('MAX(cislo) AS max_cislo')
			->where('cislo LIKE ?', '5' . $year . '%')
			->fetch()){
			return $new;
		}

		if(!$last->max_cislo){
			return $new;
		}

		return (string) (int) ($last->max_cislo + 1);
	}
}