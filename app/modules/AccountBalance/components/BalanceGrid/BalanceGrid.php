<?php

namespace App\Modules\AccountBalance\Components;

use App\Extensions\Components\BaseGridBoo;
use App\Models\Enums\KlientEnums;
use Kdyby\Translation\Translator;

class BalanceGrid extends BaseGridBoo{

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
		$this->addColumnText('odberatel', 'Odběratel')
			->setSortable()
			->setFilterText()
			->setCondition($this->getDSCallback('filterOdberatel'));

		$this->addColumnText('kind', 'Typ')
			->setSortable()
			->setReplacement(KlientEnums::$LABELS_SHORT)
			->setFilterSelect([
			null => '- vše -'
		] + KlientEnums::$LABELS_LONG)
			->setCondition($this->getDSCallback('filterKind'));

		$this->addColumnNumber('zalohy_celkem', 'Zálohy celkem')
			->setFormat(2, ',')
			->setSortable();

		$this->addColumnNumber('zalohy_splatne', 'Zálohy splatno')
			->setFormat(2, ',')
			->setSortable();

		$this->addColumnNumber('platby_zalohy', 'Platby záloh')
			->setFormat(2, ',')
			->setSortable();

		$this->addColumnNumber('zalohy_rozdil', 'Zálohy rozdíl')
			->setFormat(2, ',')
			->setRenderer([
			$this,
			'renderZalohyRozdil'
		])
			->setSortable()
			->setSortableCallback($this->getDSCallback('sortByZalohyRozdil'));

		$this->addColumnNumber('faktury_vystavene', 'Faktury vystaveno')
			->setFormat(2, ',')
			->setSortable();

		$this->addColumnNumber('platby_faktury', 'Platby faktur')
			->setFormat(2, ',')
			->setSortable();

		if($this->isAllowed('view')){
			$this->addAction('view', '', 'View:')
				->setIcon('file-text-o')
				->setTitle('Zobrazit detail')
				->setClass(self::BUTTON_ICON);
		}

		$this->setDefaultPerPage(20);
		$this->setDefaultSort([
			'odberatel' => 'ASC'
		]);
	}

	public function renderZalohyRozdil(
		$row)
	{
		return $row->platby_zalohy - $row->zalohy_splatne;
	}
}