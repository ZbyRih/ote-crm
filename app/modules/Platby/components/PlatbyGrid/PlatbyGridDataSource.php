<?php

namespace App\Modules\Platby\Components;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\PlatbaTable;
use Nette\Database\Table\Selection;
use App\Extensions\Utils\Arrays;
use Nette\Database\Context;
use App\Models\Enums\PlatbyEnums;

class PlatbyGridDataSource extends DataSourceGridBoo{

	const VIEW_ALL = 'all';

	const VIEW_ZARAZENE = 'linked';

	const VIEW_NEZARAZENE = 'alone';

	const VIEW_STAZENE = 'auto';

	const VIEW_RUCNI = 'man';

	const VIEW_OSTATNI = 'dep';

	const VIEW_S_DOKLADY = 'dokl';

	const FILTER_STAV_IN = 'in';

	const FILTER_STAV_OUT = 'out';

	protected $primary_key = 'platba_id';

	/** @var PlatbaTable */
	private $tbl;

	/** @var string */
	private $year;

	/** @var string */
	private $view;

	/** @var integer */
	private $userId;

	public function __construct(
		Context $db,
		PlatbaTable $tbl)
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

	protected function build()
	{
		$s = $this->tbl->select('platba_id AS `id`')
			->select('from_cu, type, subject, platba, preplatek, vs, man, when')
			->select('(SELECT `cislo` FROM `doklady` AS `d` WHERE `d`.`platba_id` = tx_platby.platba_id) AS `dokl_cislo`')
			->select('(SELECT COUNT(`id`) FROM `platby_zarazeni` AS `pz` WHERE `pz`.`platba_id` = tx_platby.platba_id) AS `linked`');

		if($this->year){
			$s->where('YEAR(when)', $this->year);
		}

		if($this->userId){
			$s->where(
				'vs IN (
				SELECT DISTINCT `z`.`vs` FROM
					`es_klients` AS `k`,
					`tx_sml_om` AS `sml`,
					`tx_zalohy` AS `z`
					WHERE `k`.`owner_id` = ?
					AND `k`.`deleted` = 0
					AND `k`.`active` = 1
					AND `k`.`disabled` = 0
					AND `sml`.`klient_id` = `k`.`klient_id`
					AND `z`.`odber_mist_id` = `sml`.`odber_mist_id`
					AND `z`.`klient_id` = `k`.`klient_id`
				)', $this->userId);
		}

		if($this->view != self::VIEW_OSTATNI){
			$s->whereOr([
				'NOT type' => PlatbyEnums::USE_OTHERS,
				'type' => null
			]);
		}

		if(!$this->view){
			return $s;
		}

		if($this->view == self::VIEW_ZARAZENE){
			$s->having('linked > 0');
		}elseif($this->view == self::VIEW_NEZARAZENE){
			$s->having('linked < 1');
		}elseif($this->view == self::VIEW_STAZENE){
			$s->where('man = 0');
		}elseif($this->view == self::VIEW_RUCNI){
			$s->where('man = 1');
		}elseif($this->view == self::VIEW_OSTATNI){
			$s->where('type', PlatbyEnums::USE_OTHERS);
		}elseif($this->view == self::VIEW_S_DOKLADY){
			$s->having('dokl_cislo IS NOT NULL');
			$s->having('dokl_cislo != ""');
		}

		return $s;
	}

	public function filterStav(
		Selection $s,
		$value)
	{
		if($value == self::FILTER_STAV_IN){
			$s->where('platba >= 0');
		}else if($value == self::FILTER_STAV_OUT){
			$s->where('platba < 0');
		}
	}

	public function filterCU(
		Selection $s,
		$value)
	{
		$s->where('from_cu LIKE ?', '%' . $value . '%');
	}

	public function filterType(
		Selection $s,
		$value)
	{
		$s->where('type IN (?)', Arrays::toArray($value));
	}

	public function filterPopis(
		Selection $s,
		$value)
	{
		$s->where('subject LIKE ?', '%' . $value . '%');
	}

	public function filterPlatba(
		Selection $s,
		$value)
	{
		$s->where('platba = ?', $value);
	}

	public function filterVS(
		Selection $s,
		$value)
	{
		$s->where('vs LIKE ?', '%' . $value . '%');
	}

	public function filterMan(
		Selection $s,
		$value)
	{
		$s->where('man = ?', $value);
	}

	public function filterLinked(
		Selection $s,
		$value)
	{
		if($value){
			$s->having('linked IS TRUE');
		}else{
			$s->having('linked IS NOT TRUE');
		}
	}

	public function filterDokl(
		Selection $s,
		$value)
	{
		$s->where('cislo LIKE ?', '%' . $value . '%');
	}
}