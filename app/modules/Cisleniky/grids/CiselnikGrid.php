<?php

namespace App\Modules\Ciselniky\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class CiselnikGrid extends BaseGridBoo{

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
		$this->addColumnText('value', 'Hodnota')->setSortable();
		$this->addColumnText('nazev', 'Název')->setSortable();
		$this->addColumnText('value2', 'Hodnota 2')->setSortable();
		$this->addColumnText('value3', 'Hodnota 3')->setSortable();

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
				$container->setDefaults(
					[
						'id' => $item->id,
						'nazev' => $item->nazev,
						'value' => $item->value,
						'value2' => $item->value2,
						'value3' => $item->value3
					]);
			};
		}

		if($this->isAllowed('delete')){

			$this->addActionCallback('delete', '', function (
				$id)
			{
				$this->onDelete($id);
			})
				->setTitle('Smazat')
				->setIcon('md md-delete')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu chcete položku smazat ?');
		}

		$this->setDefaultSort([
			'value' => 'ASC'
		]);
	}

	public function onInlineEditAdd(
		$container)
	{
		$container->addHidden('id');
		$container->addText('value', '', 35)->setRequired('Název musí být zadán.');
		$container->addText('nazev', '', 60)->setRequired('Hodnota musí být zadána.');
		$container->addText('value2', '', 60);
		$container->addText('value3', '', 60);
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