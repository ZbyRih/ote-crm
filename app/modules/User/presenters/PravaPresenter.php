<?php
namespace App\Modules\User\Presenters;

use App\Models\Tables\UserTable;
use App\Modules\User\Factories\IEditPrava;
use Nette\Database\Table\ActiveRow;

class PravaPresenter extends BasePresenter{

	/** @var IEditPrava @inject */
	public $comEdit;

	/** @var UserTable @inject */
	public $users;

	/** @var int @persistent */
	public $id;

	/** @var ActiveRow */
	private $usr;

	public function actionDefault(
		$id)
	{
		$this->id = $id;

		if($id && !$this->isAllowed('edit')){
			$this->redirect('Default:');
		}

		if(!$this->user->isInRole('super')){
			$this->redirect('Default:');
		}

		$this->usr = $this->id ? $this->users->find($this->id) : null;
	}

	public function renderDefault()
	{
		$this->template->setParameters([
			'id' => $this->id,
			'title' => $this->usr ? $this->usr->jmeno : ''
		]);
	}

	public function createComponentEditForm()
	{
		$com = $this->comEdit->create();
		$com->setUser($this->usr);
		return $com;
	}
}