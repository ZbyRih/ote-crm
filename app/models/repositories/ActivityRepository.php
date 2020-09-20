<?php

namespace App\Models\Repositories;

use App\Models\Tables\ActivityTable;

class ActivityRepository{

	/** @var ActivityTable */
	private $tbl;

	public function __construct(
		ActivityTable $tbl)
	{
		$this->tbl = $tbl;
	}
}