<?php

namespace App\Models\Orm\SmlOMs;

use Nextras\Orm\Mapper\Mapper;

class SmlOMsMapper extends Mapper{

	protected $tableName = 'tx_sml_om';

	public function getByOmInYear(
		$klientId,
		$year,
		$omId)
	{
		return $this->builder()
			->where('odber_mist_id = %i', $omId)
			->andWhere('klient_id = %i', $klientId)
			->andWhere('%i = YEAR(od) OR %i = YEAR(do)', $year, $year);
	}

	public function getByFakSkupInYear(
		$klientId,
		$year,
		$fakSkupId)
	{
		return $this->builder()
			->where('fak_skup_id = %i', $fakSkupId)
			->andWhere('klient_id = %i', $klientId)
			->andWhere('%i BETWEEN YEAR(od) AND YEAR(do)', $year);
	}
}