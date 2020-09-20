<?php

namespace App\Modules\Ciselniky\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\CiselnikyValuesTable;
use Nette\Database\Context;

class CiselnikGridDataSource extends DataSourceGridBoo{

	/** @var CiselnikyValuesTable */
	private $tbl;

	/** @var string */
	private $group;

	public function __construct(
		Context $db,
		CiselnikyValuesTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	/**
	 * @param string $group
	 */
	public function setGroup(
		$group)
	{
		$this->group = $group;
	}

	protected function build()
	{
		$s = $this->tbl->table()
			->select('id, value, nazev, value2, value3')
			->where('deleted', 0);
		if($this->group){
			$s->where('group', $this->group);
		}else{
			$s->where('TRUE IS FALSE');
		}

		return $s;
	}
}