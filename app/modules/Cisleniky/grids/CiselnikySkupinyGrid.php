<?php

namespace App\Modules\Ciselniky\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class CiselnikySkupinyGrid extends BaseGridBoo{

	/** @var [] */
	public $onDelete = [];

	/** @var [] */
	public $onSave = [];

	public function __construct(
		Translator $translator)
	{
		parent::__construct($translator);
	}

	protected function build()
	{
		$this->addColumnText('nazev', 'nazev')->setSortable();

		if($this->isAllowed('delete')){
			$this->addActionCallback('delete', '', function (
				$id)
			{
				$this->onDelete($id);
			})
				->setTitle('Smazat')
				->setIcon('md md-delete')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu chcete skupinu smazat ?');
		}

		if($this->isAllowed('edit')){
			$this->addInlineAdd()->onControlAdd[] = [
				$this,
				'onInlineEditAdd'
			];
			$this->addInlineEdit()->onControlAdd[] = [
				$this,
				'onInlineEditAdd'
			];

			$this->getInlineAdd()->onSubmit[] = [
				$this,
				'onInlineEditSubmit'
			];
			$this->getInlineEdit()->onSubmit[] = [
				$this,
				'onInlineEditSubmit'
			];

			$this->getInlineEdit()->onSetDefaults[] = function (
				$container,
				$item)
			{
				$container->setDefaults([
					'id' => $item->id,
					'nazev' => $item->nazev
				]);
			};
		}

		$this->setDefaultSort([
			'nazev' => 'ASC'
		]);
	}

	public function onInlineEditAdd(
		$container)
	{
		$container->addHidden('id');
		$container->addText('nazev', '', 20)->setRequired('Název musí být zadán.');
	}

	public function onInlineEditSubmit(
		$id,
		$values = null)
	{
		if(!$values){
			$values = $id;
		}else{
			$values['id'] = $id;
		}
		$this->onSave($values);
		$this->redrawControl();
	}
}