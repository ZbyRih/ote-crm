<?php

namespace App\Modules\Zalohy\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;
use App\Models\Enums\SmlOMEnums;

class ZalohyGrid extends BaseGridBoo{

	/** @var array */
	public $onZalohy = [];

	/** @var array */
	public $onOdberatel = [];

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseGridBoo::__construct()
	 */
	public function __construct(
		Translator $translator)
	{
		parent::__construct($translator);
	}

	protected function build()
	{
		$this->setDefaultPerPage(20);
		$this->setDefaultSort([
			'cis' => 'DESC'
		]);

		$this->addColumnText('com', 'ČOM')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterCOM'));

		$this->addColumnText('adresa', 'Adresa')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterAddress'));

		$this->addColumnText('odberatel', 'Odběratel')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterOdberatel'));

		$this->addColumnText('interval', 'Četnost')
			->setSortable()
			->setReplacement(SmlOMEnums::$INTERVAL_LABELS)
			->setFilterSelect([
			null => 'vše'
		] + SmlOMEnums::$INTERVAL_LABELS)
			->setCondition($this->getDSCallback('filterInterval'));

		$this->addColumnNumber('num', 'Počet')
			->setFormat(0, '')
			->setSortable();
		$this->addColumnNumber('celkem', 'Suma')->setFormat(2, ',');

		$this->addActionCallback('do_zaloh', '', function (
			$id)
		{
			$this->onZalohy($id);
		})
			->setClass(self::BUTTON_ICON)
			->setTitle('Přejít do záloh odběratele')
			->setIcon('fa fa-leanpub');

		$this->addActionCallback('do_odberatele', '', function (
			$id)
		{
			$this->onOdberatel($id);
		})
			->setClass(self::BUTTON_ICON)
			->setTitle('Přejít do odběratele')
			->setIcon('md md-account-child');
	}
}