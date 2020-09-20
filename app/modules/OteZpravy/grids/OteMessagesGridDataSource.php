<?php

namespace App\Modules\OteZpravy\Grids;

use App\Extensions\Components\DataSourceGridBoo;
use App\Models\Tables\OteMessageTable;
use Nette\Database\Context;

class OteMessagesGridDataSource extends DataSourceGridBoo{

	/** @var OteMessageTable */
	private $tbl;

	public function __construct(
		Context $db,
		OteMessageTable $tbl)
	{
		parent::__construct($db);
		$this->tbl = $tbl;
	}

	protected function build()
	{
		$sel = $this->tbl->select('id, received, ote_id, decrypted, processed, ote_kod, subject');
		return $sel;
	}
}