<?php

namespace App\Modules\Platby\Components;

use App\Extensions\App\Ciselnik;
use App\Extensions\Components\BaseGridBoo;
use App\Extensions\Utils\Html;
use App\Models\Repositories\CiselnikyValuesRepository;
use Kdyby\Translation\Translator;
use App\Extensions\Utils\Arrays;
use App\Models\Enums\PlatbyEnums;

class PlatbyGrid extends BaseGridBoo{

	const BIN_REPLACE = [
		0 => 'NE',
		1 => 'ANO'
	];

	const BIN_SELECT = [
		null => '- vše -',
		0 => 'ne',
		1 => 'ano'
	];

	/** @var [] */
	public $onChangeType = [];

	/** @var [] */
	public $onZaradit = [];

	/** @var [] */
	public $onVyradit = [];

	/** @var Ciselnik */
	private $typyPlateb;

	private static $UP = null;

	private static $DOWN = null;

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseGridBoo::__construct()
	 */
	public function __construct(
		Translator $translator,
		CiselnikyValuesRepository $repCisl)

	{
		$this->typyPlateb = $repCisl->getCiselnik('typy_pohybu');
		parent::__construct($translator);

		self::$UP = Html::el('span')->addHtml(Html::el('span')->class('text-success md-arrow-drop-up'))
			->class('text-xl');
		self::$DOWN = Html::el('span')->addHtml(Html::el('span')->class('text-danger md-arrow-drop-down'))
			->class('text-xl');
	}

	protected function build()
	{
		$this->setDefaultPerPage(20);
		$this->setDefaultSort([
			'when' => 'DESC',
			'platba_id' => 'DESC'
		]);

		$this->setColumnsHideable();

		$types = Arrays::diff($this->typyPlateb->getPairs(), [
			PlatbyEnums::USE_FAKTURA,
			PlatbyEnums::USE_ZALOHA
		]);

		$this->addGroupAction('Změnit typ na', [
			null => '- neurčen -'
		] + $types)->onSelect[] = [
			$this,
			'onSelectChangeType'
		];

		$this->addGroupAction('Zrušit zařazení')->onSelect[] = [
			$this,
			'onSelectVyradit'
		];
		$this->addGroupAction('Zařadit')->onSelect[] = [
			$this,
			'onSelectZaradit'
		];

		$this->addColumnText('color', 'Stav')
			->setRenderer([
			$this,
			'renderStav'
		])
			->setFilterSelect([
			null => '- oboje -',
			'in' => 'přípis',
			'out' => 'odpis'
		])
			->setCondition($this->getDSCallback('filterStav'));

		$types = [
			null => '- neurčen -'
		] + $this->typyPlateb->getPairs();

		$this->addColumnText('type', 'Typ')
			->setSortable()
			->setReplacement($types)
			->setFilterSelect($types)
			->setCondition($this->getDSCallback('filterType'));

		$this->addColumnText('from_cu', 'Z účtu')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterCU'));

		$this->addColumnText('subject', 'Popis')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterPopis'));

		$this->addColumnNumber('platba', 'Částka')
			->setFormat(2, ',')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterPlatba'));

		$this->addColumnText('vs', 'Var. sym.')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterVS'));

		$this->addColumnText('man', 'pořízení')
			->setSortable()
			->setReplacement([
			0 => 'stažené',
			1 => 'ruční'
		])
			->setFilterSelect([
			null => '- vše -',
			0 => 'stažené',
			1 => 'ruční'
		])
			->setCondition($this->getDSCallback('filterMan'));

		$this->addColumnText('linked', 'Zařazeno')
			->setSortable()
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterLinked'));

		$this->addColumnDateTime('when', 'Připsáno')
			->setSortable()
			->setFormat('d.m. Y')
			->setFilterDate();

		$this->addColumnText('dokl_cislo', 'Doklad č.')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterDokl'));

		if($this->isAllowed('edit')){
			$this->addAction('edit', '', 'Edit:')
				->setIcon('edit')
				->setTitle('Upravit')
				->setClass(self::BUTTON_ICON);
		}

		if($this->isAllowed('edit')){
			$this->addAction('dokl', '', 'Doklad:download')
				->setIcon(function (
				$row)
			{
				return $row->dokl_cislo ? 'md md-file-download' : 'md md-local-print-shop';
			})
				->setTitle(function (
				$row)
			{
				return $row->dokl_cislo ? 'Stáhnout příjmový doklad' : 'Vytvořit a stáhnout příjmový doklad';
			})
				->setClass(self::BUTTON_ICON);
		}

		if($this->isAllowed('nahled')){
			$this->addAction('nahled', '', 'Doklad:nahled')
				->setIcon('md md-pageview')
				->setTitle('Náhled')
				->setClass(self::BUTTON_ICON);
		}
	}

	public function renderStav(
		$row)
	{
		if($row->platba > 0){
			return self::$UP;
		}

		if($row->platba < 0){
			return self::$DOWN;
		}
	}

	public function onSelectChangeType(
		array $ids,
		$type)
	{
		$this->onChangeType($ids, $type);
	}

	public function onSelectZaradit(
		array $ids)
	{
		$this->onZaradit($ids);
	}

	public function onSelectVyradit(
		array $ids)
	{
		$this->onVyradit($ids);
	}
}