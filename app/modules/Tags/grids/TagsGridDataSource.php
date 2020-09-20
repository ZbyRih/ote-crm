<?php

namespace App\Modules\Tags\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\TagsTable;
use Nette\Database\Context;

class TagsGridDataSource extends DataSourceGridBoo{

	/** @var TagsTable */
	private $tbl;

	/** @var int */
	private $userId;

	public function __construct(
		Context $db,
		TagsTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	/**
	 * @param number $userId
	 */
	public function setUserId(
		$userId)
	{
		$this->userId = $userId;
	}

	protected function build()
	{
		return $this->tbl->select('id, name, color')->where('user_id', $this->userId);
	}
}