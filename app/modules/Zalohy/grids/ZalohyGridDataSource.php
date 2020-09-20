<?php

namespace App\Modules\Zalohy\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\ZalohaTable;
use Nette\Database\Context;
use Nette\Database\Table\Selection;

/**

 * @property bool $ommitSuper
 * @property bool $deleted
 */
class ZalohyGridDataSource extends DataSourceGridBoo{

	/** @var ZalohaTable */
	private $tbl;

	/** @var string */
	private $year;

	/** @var string */
	private $view;

	/** @var integer */
	private $userId;

	protected $primary_key = 'id';

	const VIEW_ALL = 'all';

	const VIEW_VPORADKU = 'ok';

	const VIEW_PO_SPLATNOSTI = 'pospl';

	const VIEW_UHRAZENE = 'uhr';

	public function __construct(
		Context $db,
		ZalohaTable $tbl)
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
		$s = $this->tbl->table('smlouvy_zalohy_adresy_odberatel')
			->select('id')
			->select('com')
			->select('adresa')
			->select('odberatel')
			->select('interval')
			->select('COUNT(zaloha_id) AS num')
			->select('SUM(vyse) AS celkem')
			->group('id');

		if($this->year){
			$s->where('YEAR(z_od)', $this->year);
		}

		if($this->userId){
			$s->where('k_owner_id', $this->userId);
		}

		if(!$this->view){
			return $s;
		}

		return $s;
	}

	public function filterCOM(
		Selection $s,
		$value)
	{
		$s->where('odber_mist_id IN (SELECT `odber_mist_id` FROM `tx_odber_mist` WHERE eic LIKE ? OR com LIKE ?)', '%' . $value . '%', '%' . $value . '%');
	}

	public function filterAddress(
		Selection $s,
		$value)
	{
		$s->having('adresa LIKE ?', '%' . $value . '%');
	}

	public function filterOdberatel(
		Selection $s,
		$value)
	{
		$s->having('odberatel LIKE ?', '%' . $value . '%');
	}

	public function filterInterval(
		Selection $s,
		$value)
	{
		$s->where('interval', $value);
	}
}