<?php

namespace App\Modules\Info\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class InfoGrid extends BaseGridBoo{

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
		$this->addColumnDateTime('created', 'Vytvořeno')
			->setFormat('j.n. Y H:i:s')
			->setSortable()
			->setFilterDate();
// 		$this->addColumnText('message', 'Zpráva');
		$this->addColumnText('type', 'Typ')
			->setReplacement([
			'b' => 'banka',
			'o' => 'ote'
		])
			->setSortable()
			->setFilterSelect([
			'b' => 'banka',
			'o' => 'ote'
		]);

		$this->addAction('view', 'Zobrazit');

		$this->setDefaultSort([
			'created' => 'DESC'
		]);
	}
}