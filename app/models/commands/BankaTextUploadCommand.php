<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Orm;
use App\Models\Repositories\SettingsRepository;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Strategies\BankPlatbyParseVypisPlainStrategy;
use App\Models\Strategies\BankaFilterDuplicatesStrategy;
use App\Models\Strategies\BankaFilterImportStrategy;
use App\Models\Strategies\PlatbyZaraditStrategy;
use App\Models\Strategies\VypisPlainPartToPlatbaEntityStrategy;
use App\Models\Tables\PlatbaTable;

class BankaTextUploadCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var PlatbaTable */
	private $tbl;

	/** @var InfoData */
	private $info;

	/** @var FakturaSelection */
	private $selFaks;

	/** @var ZalohaSelection */
	private $selZals;

	/** @var string */
	private $file;

	/** @var string */
	private $limit;

	/** @var float */
	private $dphCoef;

	public function __construct(
		Orm $orm,
		PlatbaTable $tbl,
		ZalohaSelection $selZals,
		FakturaSelection $selFaks,
		SettingsRepository $repSettings)
	{
		$this->orm = $orm;
		$this->tbl = $tbl;
		$this->selFaks = $selFaks;
		$this->selZals = $selZals;
		$this->dphCoef = $repSettings->dph_koef;
	}

	/**
	 * @param string $file
	 */
	public function setFile(
		$file)
	{
		$this->file = $file;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		$info)
	{
		$this->info = $info;
	}

	public function setLimit(
		$limit)
	{
		$this->limit = $limit;
	}

	public function execute()
	{
		$cnt = $this->file;

		$str = new BankPlatbyParseVypisPlainStrategy();
		$parts = $str->read($cnt);

		$this->info->add(sprintf('Ze souboru načteno %d pohybů', count($parts)), InfoEnums::INFO);

		$platby = [];

		$strConvert = new VypisPlainPartToPlatbaEntityStrategy($this->dphCoef);
		foreach($parts as $par){
			$platby[] = $strConvert->convert($par);
		}

		$platby = array_filter($platby);

		if(!$platby){
			return;
		}

		$filter = new BankaFilterImportStrategy($this->limit);
		$platby = $filter->filter($platby);

		$filter = new BankaFilterDuplicatesStrategy($this->tbl);
		$platby = $filter->filter($platby);

		$this->info->add(sprintf('Založeno %d plateb', count($platby)), InfoEnums::INFO);

		$strZaradit = new PlatbyZaraditStrategy($this->orm, $this->info, $this->selFaks, $this->selZals);
		$platby = $strZaradit->zaradit($platby);

		foreach($platby as $p){
			$this->orm->persist($p);
		}
	}
}