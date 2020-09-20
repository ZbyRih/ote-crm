<?php

namespace App\Models\Selections;

use App\Models\Tables\UserTable;

class UserSelection{

	/** @var UserTable */
	private $tbl;

	public function __construct(
		UserTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getNames()
	{
		return $this->tbl->select('jmeno, id')->fetchPairs('id', 'jmeno');
	}

	public function getForInfo(
		$type)
	{
		return $this->tbl->select('id')
			->where('info_' . $type, 1)
			->fetchAll();
	}
}