<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class FakturaPlatbyGrid extends BaseGridBoo{

	/**
	 *
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
		$this->addColumnText('from_cu', 'Z účtu');
		$this->addColumnText('vs', 'V.S.');
		$this->addColumnDateTime('when', 'Dne')->setFormat('d.m. Y');
		$this->addColumnNumber('platba', 'Platba (CZK)');
		$this->addColumnNumber('suma', 'Do faktury připsáno (CZK)');

		$this->setDefaultSort([
			'when' => 'DESC'
		]);

		$this->setPagination(false);
	}
}