<?php

namespace App\Models\Services;

use App\Models\Orm\Orm;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Strategies\PlatbyZaraditStrategy;

class PlatbyZaraditStrategyFactory{

	/** @var Orm */
	private $orm;

	/** @var FakturaSelection */
	private $selFaks;

	/** @var ZalohaSelection */
	private $selZals;

	public function __construct(
		Orm $orm,
		ZalohaSelection $selZals,
		FakturaSelection $selFaks)
	{
		$this->orm = $orm;
		$this->selFaks = $selFaks;
		$this->selZals = $selZals;
	}

	/**
	 * @return PlatbyZaraditStrategy
	 */
	public function create()
	{
		$str = new PlatbyZaraditStrategy($this->orm, null, $this->selFaks, $this->selZals);

		return $str;
	}
}