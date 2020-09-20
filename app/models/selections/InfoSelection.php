<?php

namespace App\Models\Selections;

use App\Models\Tables\InfoTable;

class InfoSelection{

	/** @var InfoTable */
	private $tbl;

	public function __construct(
		InfoTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getNew(
		$userId)
	{
// 		return $this->tbl->select('type, data, created')
// 			->order('created DESC')
// 			->fetchAll();
	}

	public function getNewCount(
		$userId)
	{
// 		if(!$r = $this->tbl->select('COUNT(id) AS num')
// 			->where('viewed IS NULL')
// 			->where('user_id', $userId)
// 			->fetch()){
// 			return 0;
// 		}
// 		return $r->num;
	}
}