<?php
namespace App\Models\Repositories;

use App\Models\Tables\OteMessageTable;

class OteMessageRepository{

	/** @var OteMessageTable */
	private $tbl;

	public function __construct(
		OteMessageTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getCodes()
	{
		return $this->tbl->select('ote_kod')
			->group('ote_kod')
			->fetchPairs('ote_kod', 'ote_kod');
	}
}