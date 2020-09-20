<?php

namespace App\Models\Selections;

use App\Models\Tables\FakturaTable;

class FakturaSelection{

	/** @var FakturaTable */
	private $tbl;

	public function __construct(
		FakturaTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getYears()
	{
		return $this->tbl->select('YEAR(vystaveno) AS y')
			->group('YEAR(vystaveno)')
			->fetchPairs('y', 'y');
	}

	public function unlinked(
		$year)
	{
		return $this->tbl->select('id, cis, preplatek, splatnost, om_id, klient_id')
			->where('storno', 0)
			->where('deleted IS NULL')
			->where('YEAR(vystaveno)', [
			$year,
			$year + 1
		])
			->where('preplatek > ?', 0)
			->where('getFakUhrDne(preplatek, id) IS NULL')
			->order('cis ASC');
	}

	public function lookByVs(
		$vs)
	{
		return $this->tbl->select('id, cis, preplatek, om_id, klient_id')
			->where('storno', 0)
			->where('deleted IS NULL')
			->where('cis', $vs)
			->fetchAll();
	}
}