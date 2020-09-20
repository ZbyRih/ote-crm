<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\FakturaTable;
use Nette\Database\Table\Selection;
use Nette\Database\Context;

/**

 * @property bool $ommitSuper
 * @property bool $deleted
 */
class FakturyGridDataSource extends DataSourceGridBoo{

	const VIEW_ALL = 'all';

	const VIEW_PREPLATKY = 'prepl';

	const VIEW_NEDOPLATKY = 'nedpl';

	const VIEW_NEUHRAZENE = 'neuhr';

	const VIEW_UHRAZENE = 'uhr';

	const VIEW_NEODESLANE = 'neods';

	const VIEW_STORNO = 'storno';

	const VIEW_RUCNE_VYTVORENE = 'man';

	protected $primary_key = 'id';

	/** @var FakturaTable */
	private $tbl;

	/** @var string */
	private $year;

	/** @var string */
	private $view;

	/** @var integer */
	private $userId;

	public function __construct(
		Context $db,
		FakturaTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	/**
	 * @param string $year
	 */
	public function setYear(
		$year)
	{
		$this->year = $year;
	}

	/**
	 * @param number $userId
	 */
	public function setUserId(
		$userId)
	{
		$this->userId = $userId;
	}

	/**
	 * @param string $view
	 */
	public function setView(
		$view)
	{
		$this->view = $view;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\DataSourceGridBoo::build()
	 */
	protected function build()
	{
		$s = $this->tbl->select('id')
			->select('IF(getFakUhrDne(preplatek, id) IS NOT NULL, 3, IF(DATE(NOW()) > DATE(splatnost), 2, IF(odeslano IS NOT NULL, 1 , 0))) color')
			->select('od, do, vystaveno, user_id, cis, man, storno, suma, dph, suma_a_dph, preplatek, spotreba')
			->select(
			'(SELECT IF(`ka`.`kind`, `ka`.`firm_name`, CONCAT_WS(\' \', `ka`.`firstname`, `ka`.`lastname`)) AS `name` FROM `klient_address` AS `ka` WHERE `ka`.`klient_id` = tx_faktury.klient_id) AS klient_name')
			->select('IF(odeslano IS NOT NULL, 1, 0) AS odeslano')
			->select('isUhrFaktura(preplatek, id) AS uhrazeno')
			->select('IF(ext IS NOT NULL OR ext != \'\', 1, 0) AS ps')
			->select('(SELECT `tom`.`com` FROM `tx_odber_mist` AS `tom` WHERE `tom`.`odber_mist_id` = `om_id`) AS com')
			->select('(SELECT `tom`.`eic` FROM `tx_odber_mist` AS `tom` WHERE `tom`.`odber_mist_id` = `om_id`) AS eic')
			->select(
			'(SELECT CONCAT_WS(\', \', `adr`.`city`, `adr`.`street`, CONCAT(`adr`.`cp`, \'/\' , `adr`.`co`))
				FROM `es_address` AS `adr`, `tx_odber_mist` AS `tom` WHERE
					`tom`.`odber_mist_id` = `om_id` AND `adr`.`address_id` = `tom`.`address_id`
				 ) AS adr');

		$s = $this->buildConditions($s);

		return $s;
	}

	public function getCount()
	{
		$s = $this->tbl->select('COUNT(id) AS _count');
		$s = $this->buildConditions($s);
		if(!$r = $s->fetch()){
			return 0;
		}

		return $r->_count;
	}

	/**
	 * @param Selection $s
	 * @return Selection
	 */
	private function buildConditions(
		Selection $s)
	{
		if($this->year){
			$s->where('YEAR(dzp)', $this->year);
		}

		if($this->userId){
			$s->where('user_id', $this->userId);
		}

		if(!$this->view){
			return $s;
		}

		if($this->view == self::VIEW_PREPLATKY){
			$s->where('preplatek < 0');
		}else if($this->view == self::VIEW_NEDOPLATKY){
			$s->where('preplatek > 0');
		}else if($this->view == self::VIEW_NEUHRAZENE){
			$s->where('isUhrFaktura(preplatek, id) IS NOT TRUE');
		}else if($this->view == self::VIEW_UHRAZENE){
			$s->where('isUhrFaktura(preplatek, id) IS TRUE');
		}else if($this->view == self::VIEW_NEODESLANE){
			$s->where('odeslano IS NULL');
		}else if($this->view == self::VIEW_STORNO){
			$s->where('storno = 1');
		}else if($this->view == self::VIEW_RUCNE_VYTVORENE){
			$s->where('man = 1');
		}

		return $s;
	}

	public function filterCOM(
		Selection $s,
		$value)
	{
		$s->where('om_id IN (SELECT `odber_mist_id` FROM `tx_odber_mist` WHERE eic LIKE ? OR com LIKE ?)', '%' . $value . '%', '%' . $value . '%');
	}

	public function filterAddress(
		Selection $s,
		$value)
	{
		$s->having('adr LIKE ?', '%' . $value . '%');
	}

	public function filterOdberatel(
		Selection $s,
		$value)
	{
		$s->having('klient_name LIKE ?', '%' . $value . '%');
	}

	public function filterUhrazeno(
		Selection $s,
		$value)
	{
		$s->having('uhrazeno ' . ($value ? 'IS TRUE' : 'IS NOT TRUE'));
	}

	public function filterOdeslano(
		Selection $s,
		$value)
	{
		$s->where('odeslano ' . ($value ? 'IS NOT NULL' : 'IS NULL'));
	}

	public function filterMan(
		Selection $s,
		$value)
	{
		$s->where('man', $value ? 1 : 0);
	}

	public function filterStorno(
		Selection $s,
		$value)
	{
		$s->where('storno', $value ? 1 : 0);
	}

	public function filterPS(
		Selection $s,
		$value)
	{
		$s->having('ps = ?', $value ? 1 : 0);
	}
}