<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class FakturyParPlatbyGrid extends BaseGridBoo{

	/** @var array */
	public $onLink = [];

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
		$this->addColumnText('odb', 'Odběratel');
		$this->addColumnText('om', 'ČOM');
		$this->addColumnText('vs', 'V.S.');
		$this->addColumnNumber('vyse', 'Fakturováno');
		$this->addColumnDateTime('splatnost', 'Splatnost');
		$this->addColumnDateTime('when', 'Příchozí');
		$this->addColumnNumber('platba', 'Platba');
		$this->addColumnText('cu', 'Číslo účtu');

		$this->addColumnNumber('preplatek', 'Přeplatek / nedoplatek')->setReplacement([
			0 => '<>',
			1 => '='
		]);

		$this->setPagination(false);

		$this->useHappyComponents(false);

		if($this->isAllowed('edit')){
			$this->addGroupAction('Spojit')->onSelect[] = function (
				$ids){
				$this->onLink($ids);
			};
		}
	}
}