<?php
namespace App\Modules\Ciselniky\Presenters;

use App\Modules\Ciselniky\Factories\ICiselnikySkupinyGrid;
use App\Modules\Ciselniky\Factories\ICiselnikySkupinyGridDataSource;
use App\Models\Repositories\CiselnikSkupinaAlreadyExists;
use App\Models\Repositories\CiselnikyGroupsRepository;

class GroupsPresenter extends BasePresenter{

	/** @var CiselnikyGroupsRepository @inject */
	public $repGroup;

	/** @var ICiselnikySkupinyGrid @inject */
	public $comGrid;

	/** @var ICiselnikySkupinyGridDataSource @inject */
	public $comGridDataSource;

	public function renderDefault()
	{
		$this['switch']->getButton(1)->setActive();
		$this->template->setParameters([
			'enableSwitch' => $this->isAllowed('add')
		]);
	}

	public function createComponentGroupsGrid()
	{
		$src = $this->comGridDataSource->create();

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onDelete[] = function (
			$id){
			$this->repGroup->delete($id);
			$this->flashSuccess('Skupina odstraněna.');
		};

		$g->onSave[] = function (
			$vals){
			try{
				$this->repGroup->save($vals);
				$this->flashSuccess('Skupina uloženo/přejmenována.');
			}catch(CiselnikSkupinaAlreadyExists $e){
				$this->flashWarning('Skupina se zadaným názvem již existuje.');
			}
		};

		return $g;
	}
}