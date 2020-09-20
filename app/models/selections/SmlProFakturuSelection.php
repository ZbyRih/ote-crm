<?php

namespace App;

use App\Models\Tables\SmlOmTable;
use Carbon\Carbon;

class SmlProFakturuSelection{

	/** @var SmlOmTable */
	private $tbl;

	public function __construct(
		SmlOmTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function get(
		$klientId,
		$omId,
		$od,
		$do)
	{
		$do = Carbon::instance($do)->subDays(1);
		return $this->tbl->select('klient_id, odber_mist_id, fak_skup_id, od, do')
			->where('odber_mist_id', $omId)
			->where('klient_id', $klientId)
			->where('od <= DATE(?)', $od)
			->where('do >= DATE(?)', $do)
			->fetchAll();
	}
}