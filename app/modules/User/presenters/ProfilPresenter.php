<?php
namespace App\Modules\User\Presenters;

use App\Models\Tables\UserTable;

class ProfilPresenter extends BasePresenter{

	/** @var UserTable @inject */
	public $users;

	/** @var int @persistent */
	public $id;

	public function actionDefault($id = null){
		if($id){
			$this->id = $id;

			if($id && !$this->isAllowed('edit')){
				$this->redirect('Default:');
			}

			if(!$id && !$this->isAllowed('add')){
				$this->redirect('Default:');
			}
		}else{
			$this->id = $this->user->getId();
		}
	}

	public function renderDefault(){
		$this->template->title = '';
		$this->template->id = $this->id;

		if($this->id && $u = $this->users->find($this->id)){
			$this->template->title = $u->jmeno;
		}
	}

	public function createComponentForm(){
		return $this->createForm();
	}
}