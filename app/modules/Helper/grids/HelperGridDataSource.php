<?php

namespace App\Modules\Helper\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\HelperTable;
use Nette\Database\Context;

/**

 * @property bool $ommitSuper
 * @property bool $deleted
 */
class HelperGridDataSource extends DataSourceGridBoo{

	/** @var HelperTable */
	private $tbl;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		HelperTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	protected function build()
	{
		$s = $this->tbl->select('id, resource');

		return $s;
	}
}