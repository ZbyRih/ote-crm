<?php
namespace App\Modules\Settings\Presenters;

use App\Modules\Settings\IComponentEditForm;
use App\Extensions\Components\NavTabs;

class DefaultPresenter extends BasePresenter{

	/** @var IComponentEditForm @inject */
	public $comForm;

	/** @var string @persistent */
	public $group;

	private static $groups = [
		'main' => 'Obecné',
		'cisl' => 'Číslování',
		'compa' => 'Obchodník',
		'platb' => 'Banka',
		'ote' => 'Ote'
	];

	public function actionDefault()
	{
		$this->group = $this->group ?: 'main';
	}

	public function createComponentForm()
	{
		$f = $this->comForm->create();
		$f->setGroup($this->group);

		$f->onSave[] = function (){
			$this->flashSuccess('Nastavení uloženo.');
			$this->redirect('default');
		};

		return $f;
	}

	public function createComponentNav()
	{
		$n = new NavTabs($this->dispatcher);
		$n->setItems(self::$groups);
		$n->setTab($this->group);

		$n->onChange[] = function (
			$nav,
			$tab){
			$this->group = $tab;
			$this->redirect('default');
		};

		return $n;
	}
}