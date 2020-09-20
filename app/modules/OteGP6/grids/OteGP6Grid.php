<?php

namespace App\Modules\OteGP6\Grids;

use App\Extensions\Components\BaseGridBoo;
use App\Models\Selections\OteInvoiceHeadSelection;
use Kdyby\Translation\Translator;

class OteGP6Grid extends BaseGridBoo{

	/** @var OteInvoiceHeadSelection */
	private $sel;

	public function __construct(

		Translator $translator,
		OteInvoiceHeadSelection $sel)
	{
		parent::__construct($translator);
		$this->sel = $sel;
	}

	protected function build()
	{
		$bin = [
			null => '- vše -',
			0 => 'NE',
			1 => 'ANO'
		];

		$type = [
			null => '- vše -',
			'C' => 'CCM',
			'A' => 'A/B'
		];

		$reason = [
			null => '- vše -'
		] + $this->sel->getReasons();

		$segment = [
			null => '- vše -'
		] + $this->sel->getSegments();

		$this->addColumnText('com', 'ČOM')
			->setFilterText()
			->setCondition([
			$this->getDataSource(),
			'filterCom'
		]);

		$this->addColumnText('from', 'Období')
			->setSortable()
			->setRenderer(function (
			$row){
			return $row->from->format('j.n. Y') . ' - ' . $row->to->format('j.n. Y');
		})
			->setFilterDate()
			->setCondition([
			$this->getDataSource(),
			'filterObdobi'
		]);

		$this->addColumnText('adr', 'O.M.')
			->setFilterText()
			->setCondition([
			$this->getDataSource(),
			'filterAdr'
		]);
		$this->addColumnNumber('priceTotal', 'Total bez DPH')->setFilterText();
		$this->addColumnText('attributes_corReason', 'Důvod')->setFilterSelect($reason);
		$this->addColumnText('attributes_segment', 'Segment')->setFilterSelect($segment);
		$this->addColumnText('type', 'Typ')->setFilterSelect($type);
		$this->addColumnText('pofId', 'Pof ID')->setFilterText();
		$this->addColumnText('depricated', 'Zahozené')->setFilterSelect($bin);
		$this->addColumnText('vyfak', 'Vyfak.')
			->setFilterSelect($bin)
			->setCondition([
			$this->getDataSource(),
			'filterVyfak'
		]);

		$this->setDefaultSort([
			'from' => 'DESC'
		]);

		$this->addAction('xml', '', 'Xml:')
			->setTitle('Ukázat XML')
			->setIcon('md md-search')
			->setClass(self::BUTTON_ICON);

		$this->addAction('nahled', '', 'Nahled:')
			->setTitle('Náhled')
			->setIcon('md md-remove-red-eye')
			->setClass(self::BUTTON_ICON);

		if($this->isAllowed('edit')){
			$this->addAction('vyfak', '', 'vyfakturovat!')
				->setTitle('Vyfakturovat')
				->setIcon('md md-add-box')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu vyfakturovat ?')
				->setRenderCondition(function (
				$row){
				return !$row->vyfak;
			});
		}

		if($this->isAllowed('delete')){
			$this->addAction('delete', '', 'delete!')
				->setTitle('Zahodit')
				->setIcon('md md-delete')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu zahodit zprávu ?')
				->setRenderCondition(function (
				$row){
				return !$row->vyfak && !$row->depricated;
			});
		}

		if($this->isAllowed('delete')){
			$this->addAction('undelete', '', 'undelete!')
				->setTitle('Obnovit')
				->setIcon('md md-restore')
				->setClass(self::BUTTON_ICON)
				->setRenderCondition(function (
				$row){
				return $row->depricated;
			});
		}
	}
}