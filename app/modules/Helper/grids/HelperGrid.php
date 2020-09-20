<?php

namespace App\Modules\Helper\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class HelperGrid extends BaseGridBoo{

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
		$this->setDefaultPerPage(20);
		$this->setDefaultSort([
			'id' => 'ASC'
		]);

		$this->addColumnText('resource', 'Modul', 'resource')->setRenderer(
			function (
				$row){
				return $this->translator->translate('app.menu.' . $row->resource);
			});

		if($this->isAllowed('edit')){
			$this->addAction('edit', '', 'Edit:')
				->setIcon('edit')
				->setTitle('Upravit')
				->setClass(self::BUTTON_ICON);
		}

		if($this->isAllowed('delete')){
			$this->addAction('delete', '', 'delete!')
				->setIcon('md md-delete')
				->setTitle('Smazat')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu smazat nápovědu ?');
		}
	}
}