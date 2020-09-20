<?php
namespace App\Modules\Role\Presenters;

use App\Models\Tables\RoleTable;
use App\Modules\Role\Factories\IEditRole;
use Nette\Database\Table\ActiveRow;

class EditPresenter extends BasePresenter{

	/** @var int @persistent */
	public $id;

	/** @var IEditRole @inject */
	public $comEditRole;

	/** @var RoleTable @inject */
	public $tblRole;

	/** @var ActiveRow|NULL */
	private $role;

	public function actionDefault($id = null){
		$this->id = $id;

		if($id && !$this->isAllowed('edit')){
			$this->redirect('Default:');
		}

		if(!$id && !$this->isAllowed('add')){
			$this->redirect('Default:');
		}

		if($id && $this->role = $this->tblRole->find($this->id)){
			if($this->role->role == 'super' && !$this->user->isInRole('super')){
				$this->redirect('Default:');
			}
		}
	}

	public function renderDefault(){
		$this->template->title = $this->role ? $this->role->nazev : '';
	}

	public function createComponentEditForm(){
		return $this->comEditRole->create()->setRole($this->role);
	}
}