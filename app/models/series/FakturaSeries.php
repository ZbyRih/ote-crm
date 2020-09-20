<?php

namespace App\Models\Series;

use App\Extensions\Abstracts\NumberSeries;
use App\Extensions\Interfaces\ISeries;
use App\Models\Tables\FakturaTable;

class FakturaSeries extends NumberSeries implements ISeries{

	/** @var FakturaTable */
	private $tbl;

	public function __construct(
		FakturaTable $tbl)
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
		// 		21PPNNNN
		// 		21200112
		$new = '2' . substr($year, -2) . '0001';

		if(!$last = $this->tbl->select('MAX(cis) AS max_cis')
			->where('cis LIKE ?', '2' . substr($year, -2) . '%')
			->fetch()){
			return $new;
		}

		if(!$last->max_cis){
			return $new;
		}

		return $last->max_cis + 1;
	}
}