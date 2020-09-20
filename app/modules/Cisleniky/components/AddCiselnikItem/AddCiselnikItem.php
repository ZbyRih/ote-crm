<?php
namespace App\Modules\Ciselniky\Components;

use App\Extensions\Components\BaseComponent;

class AddCiselnikItem extends BaseComponent{

	private $group;

	/** @var [] */
	public $onSave = [];

	public function setGroup($group){
		$this->group = $group;
		return $this;
	}

	public function createComponentForm(){
		$f = $this->createForm()
			->makeInline()
			->makeSmall();

		$f->addHidden('group');
		$f->addText('value', 'Hodnota', 60)->setRequired('Hodnota musí být zadána.');
		$f->addText('nazev', 'Název', 35)->setRequired('Název musí být zadán.');
		$f->addText('value2', 'Hodnota 2', 60);
		$f->addText('value3', 'Hodnota 3', 60);
		$f->addSubmit('add', 'Přidat');

		$f->setDefaults([
			'group' => $this->group
		]);

		$f->onSuccess[] = function ($f, $v){
			$this->onSave($f, $v);
		};

		return $f;
	}
}