<?php
namespace App\Modules\Ciselniky\Presenters;

use App\Extensions\App\PersistentItem;
use App\Extensions\App\PersistentParameterSessionStorage;
use App\Models\Repositories\CiselnikValueAlreadyExists;
use App\Models\Repositories\CiselnikyGroupsRepository;
use App\Models\Repositories\CiselnikyValuesRepository;
use App\Modules\Ciselniky\Factories\ICiselnikGrid;
use App\Modules\Ciselniky\Factories\ICiselnikGridDataSource;
use App\Extensions\Components\NavTabs;

class DefaultPresenter extends BasePresenter{

	/** @var CiselnikyValuesRepository @inject */
	public $repItems;

	/** @var CiselnikyGroupsRepository @inject */
	public $repGroups;

	/** @var ICiselnikGrid @inject */
	public $comGrid;

	/** @var ICiselnikGridDataSource @inject */
	public $comGridDataSource;

	/** @var PersistentParameterSessionStorage @inject */
	public $persistanceStorage;

	/** @var string @persistent */
	public $group;

	/** @var PersistentItem */
	private $_group;

	public function actionDefault()
	{
		$this->_group = $this->persistanceStorage->create('group', $this->getAction(true), $this->group);
		$this->_group->restore();
	}

	public function renderDefault()
	{
		$this['switch']->getButton(0)->setActive();

		$this->template->setParameters([
			'enableSwitch' => $this->isAllowed('add')
		]);
	}

	public function createComponentItems()
	{
		$nav = (new NavTabs($this->dispatcher));
		$nav->setItems($this->repGroups->getValidGroups());

		$nav->setTab($this->group, false);
		$this->_group->set($nav->getTab());

		$nav->onChange[] = function (
			$n,
			$tab){
			$this->_group->set($tab);
			$this->redirect('default');
		};

		return $nav;
	}

	public function createComponentCiselnikyGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setGroup($this->group);

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onDelete[] = function (
			$id){
			$this->repItems->delete($id);
			$this->flashSuccess('Položka odstraněna.');
		};

		$g->onSave[] = function (
			$vals){
			if(!$group = $this->group){
				$this->flashWarning('Není vybrána, nebo neexistuje žádná skupina.');
				return;
			}

			$vals['group'] = $group;

			try{
				$this->repItems->save($vals);
				$this->flashSuccess('Položka upravena.');
			}catch(CiselnikValueAlreadyExists $e){
				$this->flashWarning('Položka se zadanou hodnotou již existuje.');
			}
		};
		return $g;
	}
}