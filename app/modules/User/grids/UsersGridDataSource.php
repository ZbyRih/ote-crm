<?php

namespace App\Modules\User\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\UserTable;
use Nette\Database\Context;

class UsersGridDataSource extends DataSourceGridBoo{

	/** @var UserTable */
	private $tbl;

	/** @var bool */
	private $ommitSuper = true;

	protected $primary_key = 'id';

	public function __construct(
		Context $db,
		UserTable $tbl)
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

	protected function build()
	{
		$select = $this->tbl->select('id, login, role, jmeno, activity, deleted');

		if($this->ommitSuper){
			$select->where('role != ?', 'super');
		}

		return $select;
	}
}