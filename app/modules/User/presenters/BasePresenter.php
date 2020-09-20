<?php

namespace App\Modules\User\Presenters;

class BasePresenter extends \App\Presenters\BasePresenter{

	public function getResource(){
		return 'User';
	}
}