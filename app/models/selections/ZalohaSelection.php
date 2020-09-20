<?php

namespace App\Models\Selections;

use App\Models\Tables\ZalohaTable;
use Nette\Database\Context;
use App\Models\Core\DateRange;

class ZalohaSelection{

	/** @var Context */
	private $context;

	/** @var ZalohaTable */
	private $tbl;

	public function __construct(
		Context $context,
		ZalohaTable $tbl)
	{
		$this->tbl = $tbl;
		$this->context = $context;
	}

	public function getYears()
	{
		return $this->tbl->select('YEAR(od) AS y')
			->group('YEAR(od)')
			->fetchPairs('y', 'y');
	}

	public function lookByVs(
		$vs,
		\DateTimeInterface $when)
	{
		$sel = $this->context->query(
			'SELECT
				z.vs, z.`zaloha_id`, z.`odber_mist_id`, z.`klient_id`, z.`vyse`, z.`od`, so.`fak_skup_id`, so.`klient_id` AS soKliId, so.`odber_mist_id` AS soOmId
			FROM
			    tx_zalohy AS z,
			    tx_sml_om AS so
			WHERE
				z.vs = ?
				AND DATE(?) BETWEEN DATE(z.`od`) AND DATE(z.`do`)
				AND so.`klient_id` = z.`klient_id`
				AND so.`odber_mist_id` = z.`odber_mist_id`
				AND DATE(z.`od`) BETWEEN DATE(so.`od`) AND DATE(so.`do`)
				AND DATE(z.`do`) BETWEEN DATE(so.`od`) AND DATE(so.`do`)
		', $vs, $when);

		return $sel->fetchAll();
	}

	public function getByOmIdAndRange(
		$omId,
		$klientId,
		DateRange $range)
	{
		$sel = $this->tbl->select('zaloha_id, odber_mist_id, vyse, od, do')
			->where('odber_mist_id', $omId)
			->where('klient_id', $klientId)
			->whereOr(
			[
				'DATE(od) BETWEEN DATE(\'' . $range->od . '\') AND DATE(\'' . $range->do . '\')',
				'DATE(do) BETWEEN DATE(\'' . $range->od . '\') AND DATE(\'' . $range->do . '\')'
			])
			->order('od ASC');

		return $sel->fetchAll();
	}
}