<?php

namespace App\Extensions\Components;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Ublaboo\DataGrid\DataSource\NetteDatabaseTableDataSource;

abstract class DataSourceGridBoo extends NetteDatabaseTableDataSource{

	/** @var Context */
	private $db;

	public function __construct(
		Context $db)
	{
		$this->db = $db;
	}

	/**
	 * @return Selection
	 */
	abstract protected function build();

	public function create()
	{
		$this->data_source = $this->build();
	}

	public function getCount()
	{
		$sql = $this->data_source->getSql();
		$sqlb = $this->data_source->getSqlBuilder();

		$r = $this->db->query('SELECT COUNT(*) AS count FROM (' . $sql . ') AS _count', ...$sqlb->getParameters())
			->fetch();

		return $r['count'];
	}
}