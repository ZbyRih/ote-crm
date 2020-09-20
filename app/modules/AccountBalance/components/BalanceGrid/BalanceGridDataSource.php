<?php

namespace App\Modules\AccountBalance\Components;

use App\Extensions\Components\DataSourceGridBoo;
use Nette\Database\Context;
use Nette\Database\Table\Selection;

class BalanceGridDataSource extends DataSourceGridBoo{

	/** @var Context */
	private $context;

	/** @var integer */
	private $year;

	public function __construct(
		Context $context)
	{
		parent::__construct($context);
		$this->context = $context;
	}

	/**
	 * @param number $year
	 */
	public function setYear(
		$year)
	{
		$this->year = $year;
	}

	protected function build()
	{
		$s = $this->context->table('klient_basic')
			->select('id, odberatel, kind, fakturacni, deleted')
			->select('
				(SELECT
         			SUM(`z`.`vyse`)
     			FROM
         			`tx_zalohy` AS `z`
     			WHERE YEAR(`z`.`od`) = ?
         			AND `z`.`klient_id` = `klient_basic`.`id`) AS zalohy_celkem
			', $this->year)
			->select(
			'
			     (SELECT
			         SUM(`z`.`vyse`)
			     FROM
			         `tx_zalohy` AS `z`
			     WHERE YEAR(`z`.`od`) = ?
			         AND DATE(`z`.`do`) <= DATE(NOW())
			         AND `z`.`klient_id` = `klient_basic`.`id`) AS zalohy_splatne
			', $this->year)
			->select(
			'
			    (SELECT
			        SUM(`f`.`preplatek`)
			    FROM
			        `tx_faktury` AS `f`
			    WHERE YEAR(`f`.`vystaveno`) = ?
			        AND `f`.`klient_id` = `klient_basic`.`id`) AS faktury_vystavene
			', $this->year)
			->select(
			'
			    (SELECT
			        SUM(`p`.`platba`)
			    FROM
			        `tx_platby` AS `p`, `platby_zarazeni` AS `pz`
			    WHERE YEAR(`p`.`when`) = ?
			        AND `p`.`type` = "f"
			        AND `p`.`platba_id` = `pz`.`platba_id`
			        AND `pz`.`klient_id` = `klient_basic`.`id`) AS platby_faktury
			', $this->year)
			->select(
			'
			    (SELECT
			        SUM(`p`.`platba`)
			    FROM
			        `tx_platby` AS `p`, `platby_zarazeni` AS `pz`
			    WHERE YEAR(`p`.`when`) = ?
			        AND `p`.`type` = "z"
			        AND `p`.`platba_id` = `pz`.`platba_id`
			        AND `pz`.`klient_id` = `klient_basic`.`id`) AS platby_zalohy
			', $this->year);

		$s->where('deleted = ?', 0);
		$s->where('fakturacni = ?', 0);

		return $s;
	}

	public function filterOdberatel(
		Selection $s,
		$value)
	{
		$s->where('odberatel LIKE ?', '%' . $value . '%');
	}

	public function filterKind(
		Selection $s,
		$value)
	{
		$s->where('kind', $value);
	}

	public function sortByZalohyRozdil(
		Selection $s,
		$sort)
	{
		$s->order('(IFNULL(platby_zalohy, 0) - IFNULL(zalohy_splatne, 0)) ' . $sort['zalohy_rozdil']);
	}
}