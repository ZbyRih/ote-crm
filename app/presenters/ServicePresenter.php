<?php

namespace App\Presenters;

use App\Components\Service\IServices;
use App\Models\Repositories\UserRepository;

class ServicePresenter extends BasePresenter{

	/** @var IServices @inject */
	public $comServices;

	/** @var UserRepository @inject */
	public $userRep;

	public function renderDefault(){
	}

	public function createComponentServices(){
		return $this->comServices->create()->setGetters([
			'activeUserCounts' => function (){
				return $this->userRep->getActiveUsersSum(10);
			}
		]);
	}
}