<?php

namespace App\Modules\OteZpravy\Grids;

use App\Extensions\Components\BaseGridBoo;
use App\Extensions\Utils\Html;
use App\Models\Repositories\OteMessageRepository;
use Kdyby\Translation\Translator;

class OteMessagesGrid extends BaseGridBoo{

	/** @var OteMessageRepository */
	private $rep;

	public function __construct(
		OteMessageRepository $rep,
		Translator $translator)
	{
		parent::__construct($translator);
		$this->rep = $rep;
	}

	protected function build()
	{
		$bin = [
			null => '- vše -',
			0 => 'NE',
			1 => 'ANO'
		];

		$binRep = [
			0 => Html::el('i')->class('fa fa-close text-danger'),
			1 => Html::el('i')->class('fa fa-check text-success')
		];

		$this->addColumnDateTime('received', 'Obdrženo', 'received')
			->setSortable()
			->setFilterDate();
		$this->addColumnText('ote_id', 'OTE ID')->setFilterText();
		$this->addColumnText('decrypted', 'Rozšifrováno')
			->setReplacement($binRep)
			->setFilterSelect($bin);
		$this->addColumnText('processed', 'Zpracováno')
			->setReplacement($binRep)
			->setFilterSelect($bin);
		$this->addColumnText('ote_kod', 'Kód')->setFilterSelect($this->rep->getCodes());
		$this->addColumnText('subject', 'Předmět');

		$this->addAction('view', '', 'View:')
			->setIcon('md md-remove-red-eye')
			->setClass(self::BUTTON_ICON)
			->setTitle('Náhled')
			->setRenderCondition(function (
			$row)
		{
			return $row->decrypted;
		});

		$this->setDefaultSort([
			'received' => 'DESC'
		]);
	}
}