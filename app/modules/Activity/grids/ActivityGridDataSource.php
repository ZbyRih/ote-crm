<?php

namespace App\Modules\OteZpravy\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\ActivityTable;
use Nette\Database\Context;

class ActivityGridDataSource extends DataSourceGridBoo{

	/** @var ActivityTable */
	private $tbl;

	public function __construct(
		Context $db,
		ActivityTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	protected function build()
	{
		$sel = $this->tbl->select('id, kdy, modul, resource, aktivita, popis, master, user_id')->select(
			'(SELECT `jmeno` FROM `user` WHERE `id` = user_id) AS jmeno');

		return $sel;
	}
}