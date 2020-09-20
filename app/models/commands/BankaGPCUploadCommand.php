<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\ABO\GPCFileParser;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Orm;
use App\Models\Repositories\SettingsRepository;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Strategies\BankaFilterDuplicatesStrategy;
use App\Models\Strategies\BankaFilterImportStrategy;
use App\Models\Strategies\PlatbyZaraditStrategy;
use App\Models\Strategies\ABO\GPCItemToPlatbaEntityStrategy;
use App\Models\Strategies\ABO\GPCItemsStornoFilterStrategy;
use App\Models\Tables\PlatbaTable;
use App\Models\Strategies\ABO\GPCItemsUcetFilterStrategy;

class BankaGPCUploadCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var PlatbaTable */
	private $tbl;

	/** @var string */
	private $file;

	/** @var InfoData */
	private $info;

	/** @var string */
	private $limit;

	/** @var string */
	private $obchCU;

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
		$this->obchCU = $repSettings->cislo_uctu;
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
		$reader = new GPCFileParser($this->info);

		if(!$handle = fopen($this->file, "r")){
			return;
		}

		$items = $reader->parse($handle);
		$this->info->add(sprintf('Z importu načteno %d plateb', count($items)), InfoEnums::INFO);

		fclose($handle);

		$filterStorno = new GPCItemsStornoFilterStrategy();
		$items = $filterStorno->filter($items);

		$filterObchUcet = new GPCItemsUcetFilterStrategy($this->obchCU);
		$items = $filterObchUcet->filter($items);

		$strConvert = new GPCItemToPlatbaEntityStrategy($this->dphCoef);
		foreach($items as $i){
			$platby[] = $strConvert->convert($i);
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