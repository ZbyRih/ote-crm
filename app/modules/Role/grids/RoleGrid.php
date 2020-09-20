<?php

namespace App\Modules\Role\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class RoleGrid extends BaseGridBoo{

	/** @var [] */
	public $onEdit = [];

	/** @var [] */
	public $onDelete = [];

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
		$this->addColumnText('role', 'Název')->setSortable();

		if($this->isAllowed('edit')){
			$this->addActionCallback('edit', '', function (
				$id){
				$this->onEdit($id);
			})
				->setIcon('edit')
				->setTitle('Upravit')
				->setClass(self::BUTTON_ICON . '');
		}

		if($this->isAllowed('delete')){
			$this->addActionCallback('delete', '', function (
				$id){
				$this->onDelete($id);
			})
				->setIcon(function (
				$item){
				return $item->deleted ? 'ban' : 'check';
			})
				->setTitle(function (
				$item){
				return $item->deleted ? 'Zapnout' : 'Vypnout';
			})
				->setClass(function (
				$item){
				return self::BUTTON_ICON . ($item->deleted ? ' btn-default-light' : ' btn-success');
			})
				->setConfirm('Opravdu vypnout roli %s?', 'role');
		}

		$this->setDefaultSort('role');
	}
}