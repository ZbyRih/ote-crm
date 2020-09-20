<?php

namespace App\Models\Selections;

use App\Models\Tables\OteHeadTable;

class OteInvoiceHeadSelection{

	/** @var OteHeadTable */
	private $tbl;

	public function __construct(
		OteHeadTable $tbl)
	{
		$this->tbl = $tbl;
	}

	public function getSegments()
	{
		return $this->tbl->select('attributes_segment')
			->group('attributes_segment')
			->fetchPairs('attributes_segment', 'attributes_segment');
	}

	public function getReasons()
	{
		return $this->tbl->select('attributes_corReason')
			->group('attributes_corReason')
			->fetchPairs('attributes_corReason', 'attributes_corReason');
	}
}