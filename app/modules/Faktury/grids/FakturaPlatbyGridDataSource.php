<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\PairingTable;
use Nette\Database\Context;

/**

 */
class FakturaPlatbyGridDataSource extends DataSourceGridBoo{

	/** @var PairingTable */
	private $tbl;

	/** @var integer */
	private $fakturaId;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		PairingTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	/**
	 * @param number $fakturaId
	 */
	public function setFakturaId(
		$fakturaId)
	{
		$this->fakturaId = $fakturaId;
	}

	protected function build()
	{
		$s = $this->tbl->table('par_platby')
			->select('*')
			->where('faktura_id', $this->fakturaId);

		$s->order('when DESC');

		return $s;
	}
}