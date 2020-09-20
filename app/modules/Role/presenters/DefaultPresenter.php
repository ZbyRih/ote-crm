<?php
namespace App\Modules\Role\Presenters;

use App\Models\Events\DBCommitEvent;
use App\Modules\Role\Factories\IRoleGrid;
use App\Modules\Role\Factories\IRoleGridDataSource;
use Carbon\Carbon;

class DefaultPresenter extends BasePresenter{

	/** @var IRoleGrid @inject */
	public $comGrid;

	/** @var IRoleGridDataSource @inject */
	public $comGridDataSource;

	public function createComponentRoleGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setOmmitSuper(!$this->user->isInRole('super'));
		$src->setOmmitDeleted(!$this->user->isInRole('super'));

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onEdit[] = function (
			$id)
		{
			$this->redirect('Edit:', $id);
		};

		$g->onDelete[] = function (
			$id) use (
		$g)
		{
			if($r = $this->orm->roles->getById($id)){
				$r->deleted = $r->deleted ? null : Carbon::now();
				$this->orm->persist($r);
				$this->dispatcher->dispatch(DBCommitEvent::NAME);
				$this->flashSuccess('Role ' . $r->deleted ? 'zakázána.' : 'povolena.');
			}

			if($this->isAjax()){
				$g->redrawItem($id);
			}else{
				$this->redirect('this');
			}
		};

		return $g;
	}
}