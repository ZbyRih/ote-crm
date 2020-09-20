<?php

namespace App\Models\Repositories;

use App\Models\Tables\InfoTable;

class InfoRepository{

	/** @var InfoTable */
	private $tbl;

	public function __construct(
		InfoTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function updateViewed(
		$userId)
	{
		$this->tbl->table()
			->where('viewed IS NULL')
			->where('user_id', $userId)
			->update([
			'viewed' => new \DateTime()
		]);
	}
}