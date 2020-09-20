<?php

namespace App\Models\Selections;

use App\Models\Tables\FakSkupTable;

class FakSkupSelection{

	/** @var FakSkupTable  */
	public $tbl;

	public function __construct(
		FakSkupTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getList(
		$klientId = null)
	{
		$sel = $this->tbl->select('fak_skup_id, CONCAT(cis, \' \', nazev) AS label');

		if($klientId){
			$sel->where('klient_id', $klientId);
		}

		return $sel->fetchPairs('fak_skup_id', 'label');
	}
}