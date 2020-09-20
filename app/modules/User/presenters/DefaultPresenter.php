<?php
namespace App\Modules\User\Presenters;

use App\Models\Events\DBCommitEvent;
use App\Modules\User\Factories\IUserGrid;
use App\Modules\User\Factories\IUserGridDataSource;
use Carbon\Carbon;

class DefaultPresenter extends BasePresenter{

	/** @var IUserGrid @inject */
	public $comGrid;

	/** @var IUserGridDataSource @inject */
	public $comGridDataSource;

	public function createComponentUsersGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setOmmitSuper(!$this->user->isInRole('super'));

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onEdit[] = function (
			$id){
			$this->redirect('Edit:', $id);
		};

		$g->onDelete[] = function (
			$id){
			$user = $this->orm->users->getById($id);
			$user->deleted = Carbon::now();

			$this->orm->persist($user);

			$this->dispatcher->dispatch(DBCommitEvent::NAME);

			$this->flashSuccess('Uživatel zakázán.');
			$this->redirect('Default:');
		};

		$g->onActivate[] = function (
			$id){
			$user = $this->orm->users->getById($id);
			$user->deleted = null;

			$this->orm->persist($user);

			$this->dispatcher->dispatch(DBCommitEvent::NAME);

			$this->flashSuccess('Uživatel povolen.');
			$this->redirect('Default:');
		};

		$g->onRelog[] = function (
			$id){
			$this->redirect(':Sign:Actions:switch', $id);
		};

		return $g;
	}
}