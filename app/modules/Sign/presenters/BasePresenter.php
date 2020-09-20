<?php

namespace App\Modules\Sign\Presenters;

class BasePresenter extends \App\Presenters\BasePresenter{

	public function getResource(){
		return 'Sign';
	}

	public function goToHomePage(){
		if(!$identity = $this->getIdentity()){
			$this->redirect(":Homepage:");
		}

		if($identity->home){
			$this->redirect($identity->home . ':');
		}else{
			$this->redirect(":Homepage:");
		}
	}

	public function goToIn(){
		$this->redirect("In:");
	}
}