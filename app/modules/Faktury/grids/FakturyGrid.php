<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\BaseGridBoo;
use App\Extensions\Utils\Html;
use App\Models\Selections\UserSelection;
use Kdyby\Translation\Translator;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\Column\ColumnDateTime;

class FakturyGrid extends BaseGridBoo{

	/** @var UserSelection */
	private $selUser;

	/** @var array */
	public $onPrikaz = [];

	const BIN_REPLACE = [
		0 => 'NE',
		1 => 'ANO'
	];

	const BIN_SELECT = [
		null => ' - vše - ',
		0 => 'ne',
		1 => 'ano'
	];

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseGridBoo::__construct()
	 */
	public function __construct(
		Translator $translator,
		UserSelection $selUser)
	{
		parent::__construct($translator);
		$this->selUser = $selUser;
	}

	protected function build()
	{
		$users = $this->selUser->getNames();

		$this->setDefaultPerPage(20);
		$this->setDefaultSort([
			'cis' => 'DESC'
		]);

		$this->setColumnsHideable();

		$this->addColumnText('color', 'Stav')->setRenderer([
			$this,
			'renderStav'
		]);

		$this->addColumnDateTime('od', 'Od')
			->setFormat('d.m. Y')
			->setSortable();
		$this->addColumnDateTime('do', 'Do')
			->setFormat('d.m. Y')
			->setSortable();

		$this->addColumnText('klient_name', 'Odběratel')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterOdberatel'));

		$this->addColumnDateTime('vystaveno', 'Vystaveno')
			->setDefaultHide()
			->setFormat('d.m. Y')
			->setSortable()
			->setFilterDate();

		$this->addColumnText('user_id', 'Vystavil')
			->setDefaultHide()
			->setReplacement($users)
			->setFilterSelect([
			null => ' - vše - '
		] + $users);

		$this->addColumnText('cis', 'Číslo')
			->setSortable()
			->setFilterText();

		$this->addColumnText('man', 'Uživatelská')
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterMan'));

		$this->addColumnText('storno', 'Stornována')
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterStorno'));

		$this->addColumnText('odeslano', 'Odeslána')
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterOdeslano'));

		$this->addColumnNumber('suma', 'Suma')->setFormat(2, ',', ' ');
		$this->addColumnNumber('dph', 'DPH')->setFormat(2, ',', ' ');
		$this->addColumnNumber('suma_a_dph', 'Suma vč. DPH')->setFormat(2, ',', ' ');
		$this->addColumnNumber('preplatek', 'Fakturováno')->setFormat(2, ',', ' ');

		$this->addColumnText('uhrazeno', 'Uhrazena')
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterUhrazeno'));

		$this->addColumnText('ps', 'P.S.')
			->setReplacement(self::BIN_REPLACE)
			->setFilterSelect(self::BIN_SELECT)
			->setCondition($this->getDSCallback('filterPS'));

		$this->addColumnText('com', 'ČOM')
			->setFilterText()
			->setCondition($this->getDSCallback('filterCOM'));

		$this->addColumnText('adr', 'Adr.')
			->setFilterText()
			->setCondition($this->getDSCallback('filterAddress'));

		if($this->isAllowed('edit')){
			$this->addAction('edit', '', 'Edit:')
				->setIcon('edit')
				->setTitle('Upravit')
				->setClass(self::BUTTON_ICON);
		}

		if($this->isAllowed('delete')){
			$this->addAction('storno', '', 'storno')
				->setIcon('md md-cancel')
				->setTitle('Storno')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu stornovat fakturu ?');
		}

		if($this->isAllowed('edit')){
			$this->addAction('send', '', 'send')
				->setIcon('md md-send')
				->setTitle('Označit jako odeslanou')
				->setClass(self::BUTTON_ICON)
				->setRenderCondition(function (
				$row)
			{
				return !$row->odeslano;
			});
		}

		$this->addAction('nahled', '', 'Nahled:')
			->setIcon('md md-search')
			->setTitle('Zobrazit náhled')
			->setClass(self::BUTTON_ICON);

		$this->addAction('download', '', 'download')
			->setIcon('md md-file-download')
			->setTitle('Stáhnout pdf')
			->setClass(self::BUTTON_ICON);

		if($this->isAllowed('edit')){
			$this->addAction('recreate', '', 'recreate')
				->setIcon('md md-refresh')
				->setTitle('Znovu vygenerovat pdf (předchozí verze bude ztracena)')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu znvu vygenerovat pdf (předchozí verze bude ztracena) ?')
				->setRenderCondition(function (
				$row)
			{
				return !$row->man;
			});
		}

		if($this->isAllowed('edit')){
			$this->addGroupAction('Vygenerovat bankovní převody')->onSelect[] = function (
				array $ids)
			{
				$this->onPrikaz($ids);
			};
		}

		$eic = new ColumnText($this, 'eic', 'eic', 'EIC');
		$od = new ColumnDateTime($this, 'od', 'od', 'Od');
		$do = new ColumnDateTime($this, 'do', 'do', 'Do');
		$cis = new ColumnText($this, 'cis', 'cis', 'Číslo');
		$suma = new ColumnNumber($this, 'suma', 'suma', 'Suma');
		$suma->setFormat(2, ',', '');
		$sumaADph = new ColumnNumber($this, 'suma_a_dph', 'suma_a_dph', 'Suma a DPH');
		$sumaADph->setFormat(2, ',', '');
		$spotreba = new ColumnNumber($this, 'spotreba', 'spotreba', 'spotřeba');
		$spotreba->setFormat(2, ',', '');
		$storno = new ColumnDateTime($this, 'storno', 'storno', 'Storno');

		$this->addExportCsv('Export do CSV - vše', 'fakturovano-all.csv')->setColumns([
			$eic,
			$od,
			$do,
			$cis,
			$suma,
			$sumaADph,
			$spotreba,
			$storno
		]);
	}

	public function renderStav(
		$row)
	{
		if(!$row->color){
			return null;
		}

		if($row->color == 1){
			return Html::el('span')->class('text-info md md-send');
		}

		if($row->color == 2){
			return Html::el('span')->class('text-danger md md-error');
		}

		if($row->color == 3){
			return Html::el('span')->class('text-success md md-check-box');
		}
	}
}
