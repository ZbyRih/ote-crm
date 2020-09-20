<?php
namespace App\Modules\User\Presenters;

use App\Modules\User\Factories\IEditUser;
use App\Models\Tables\UserTable;
use Nette\Database\Table\ActiveRow;

class EditPresenter extends BasePresenter{

	/** @var IEditUser @inject */
	public $comEdit;

	/** @var UserTable @inject */
	public $users;

	/** @var int @persistent */
	public $id;

	/** @var ActiveRow|null */
	private $usr;

	public function actionDefault(
		$id = null)
	{
		$this->id = $id;

		if($this->id && !$this->isAllowed('edit')){
			$this->redirect('Default:');
		}

		if(!$this->id && !$this->isAllowed('add')){
			$this->redirect('Default:');
		}

		$this->usr = $this->id ? $this->users->find($this->id) : null;
	}

	public function renderDefault()
	{
		$this->template->id = $this->id;
		$this->template->title = $this->usr ? $this->usr->jmeno : '';
	}

	public function createComponentEditForm()
	{
		$com = $this->comEdit->create();
		$com->setUser($this->usr);

		return $com;
	}
}