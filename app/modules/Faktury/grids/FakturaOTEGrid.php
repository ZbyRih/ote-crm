<?php

namespace App\Modules\Faktury\Grids;

use App\Extensions\Components\BaseGridBoo;
use App\Models\Enums\OteEnums;
use Kdyby\Translation\Translator;

class FakturaOTEGrid extends BaseGridBoo{

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
		$bin = [
			0 => 'NE',
			1 => 'ANO'
		];

		$this->addColumnText('od - do', 'Od - Do')->setRenderer(
			function (
				$row){
				return $row->from->format('d.m. Y') . ' - ' . $row->to->format('d.m. Y');
			});

		$this->addColumnText('ote_kod', 'kód');
		$this->addColumnText('pofId', 'POF ID');
		$this->addColumnText('adr', 'ČOM, adresa');
		$this->addColumnText('attributes_segment', 'Segment')->setReplacement(OteEnums::$SEGMENT);
		$this->addColumnText('attributes_corReason', 'Důvod')->setReplacement(OteEnums::$COR_REASON);
		$this->addColumnNumber('priceTotal', 'Total bez DPH');
		$this->addColumnNumber('cancelled', 'Zrušená')->setReplacement($bin);
		$this->addColumnNumber('vyfak', 'Vyfakturovaná')->setReplacement($bin);

		$this->setDefaultSort([
			'from' => 'DESC'
		]);

		$this->setPagination(false);
	}
}