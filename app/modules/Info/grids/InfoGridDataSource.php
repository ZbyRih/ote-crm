<?php

namespace App\Modules\Info\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\InfoTable;
use Nette\Database\Context;

/**

 * @property bool $ommitSuper
 * @property bool $deleted
 */
class InfoGridDataSource extends DataSourceGridBoo{

	/** @var InfoTable */
	private $tbl;

	/** @var int */
	private $userId;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		InfoTable $tbl)
	{
		$this->tbl = $tbl;
		parent::__construct($db);
	}

	protected function build()
	{
		return $this->tbl->select('id, created, type');
	}
}