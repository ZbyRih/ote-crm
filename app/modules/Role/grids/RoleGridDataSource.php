<?php

namespace App\Modules\Role\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\RoleTable;
use Nette\Database\SqlLiteral;
use Nette\Database\Context;

/**

 * @property bool $ommitSuper
 * @property bool $deleted
 */
class RoleGridDataSource extends DataSourceGridBoo{

	/** @var RoleTable */
	private $tbl;

	/** @var bool */
	private $ommitSuper = true;

	/** @var bool */
	private $ommitDeleted = true;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		RoleTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	public function setOmmitSuper(
		$ommitSuper)
	{
		$this->ommitSuper = $ommitSuper;
		return $this;
	}

	public function setOmmitDeleted(
		$ommitDeleted)
	{
		$this->ommitDeleted = $ommitDeleted;
		return $this;
	}

	protected function build()
	{
		$select = $this->tbl->select('id, role, deleted');

		if($this->ommitSuper){
			$select->where('role != ?', 'super');
		}

		if($this->ommitDeleted){
			$select->whereOr([
				'deleted > ?' => new SqlLiteral('NOW()'),
				'deleted' => null
			]);
		}

		return $select;
	}
}