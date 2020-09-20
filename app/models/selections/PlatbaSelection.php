<?php

namespace App\Models\Selections;

use App\Models\Tables\PlatbaTable;
use Nette\Database\IRow;

class PlatbaSelection{

	/** @var PlatbaTable */
	private $tbl;

	public function __construct(
		PlatbaTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getYears()
	{
		return $this->tbl->select('YEAR(when) AS y')
			->group('YEAR(when)')
			->fetchPairs('y', 'y');
	}

	/**
	 * @param int $fakSkupId
	 * @param int $klientId
	 * @param \DateTimeInterface $from
	 * @param \DateTimeInterface $to
	 * @param string $type
	 * @return IRow[]
	 */
	public function getByFakSkupIdAndRange(
		$fakSkupId,
		$klientId,
		$from,
		$to,
		$type)
	{
		$sel = $this->tbl->select('platba, when, dph_coef')
			->where('type', $type)
			->where('platba_id IN (SELECT `pz`.`platba_id` FROM `platby_zarazeni` AS `pz` WHERE `pz`.`klient_id` = ? AND `pz`.`fakskup_id` = ?)', $klientId,
			$fakSkupId)
			->where('DATE(when) BETWEEN DATE(?) AND DATE(?)', $from, $to);

		return $sel->fetchAll();
	}

	/**
	 * @param int $omId
	 * @param int $klientId
	 * @param \DateTimeInterface $from
	 * @param \DateTimeInterface $to
	 * @param string $type
	 * @return IRow[]
	 */
	public function getByOmIdAndRange(
		$omId,
		$klientId,
		$from,
		$to,
		$type)
	{
		$sel = $this->tbl->select('platba, when, dph_coef')
			->where('type', $type)
			->where('platba_id IN (SELECT `pz`.`platba_id` FROM `platby_zarazeni` AS `pz` WHERE `pz`.`klient_id` = ? AND `pz`.`om_id` = ?)', $klientId, $omId)
			->where('DATE(when) BETWEEN DATE(?) AND DATE(?)', $from, $to);

		return $sel->fetchAll();
	}
}