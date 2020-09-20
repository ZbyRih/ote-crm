<?php

namespace App\Modules\Tags\Grids;

use App\Extensions\Components\BaseGridBoo;
use Kdyby\Translation\Translator;

class TagsGrid extends BaseGridBoo{

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
		$this->addColumnText('name', 'Název')->setSortable();
		$this->addColumnText('color', 'Barva')->setSortable();

		$this->setDefaultSort([
			'name' => 'ASC'
		]);

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
				$item){
				$container->setDefaults([
					'id' => $item->id,
					'name' => $item->name,
					'color' => $item->color
				]);
			};
		}

		if($this->isAllowed('delete')){
			$this->addActionCallback('delete', '', function (
				$id){
				$this->onDelete($id);
			})
				->setTitle('Smazat')
				->setIcon('md md-delete')
				->setClass(self::BUTTON_ICON)
				->setConfirm('Opravdu chcete tag smazat ?');
		}
	}

	public function onInlineEditAdd(
		$container)
	{
		$container->addHidden('id');
		$container->addText('name', '', 25)->setRequired('Hodnota musí být zadána.');
		$container->addText('color', '', 6);
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