<?php

namespace App\Modules\Ciselniky\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\CiselnikySkupinyTable;
use Nette\Database\Context;

class CiselnikySkupinyGridDataSource extends DataSourceGridBoo{

	/** @var CiselnikySkupinyTable */
	private $tbl;

	public function __construct(
		Context $db,
		CiselnikySkupinyTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	protected function build()
	{
		$s = $this->tbl->select('id, nazev')->where('deleted', 0);

		return $s;
	}
}