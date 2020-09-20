<?php

namespace App\Modules\Sign\Presenters;

use App\Extensions\App\User\IdentityFactory;
use App\Models\Tables\UserTable;
use Nette\Security\AuthenticationException;

class ActionsPresenter extends BasePresenter{

	/** @var UserTable @inject */
	public $users;

	/** @var IdentityFactory @inject */
	public $facIdentity;

	public function actionOut()
	{
		$this->getUser()->logout(true);
		$this->goToIn();
	}

	public function actionBack()
	{
		if(!$user = $this->getUser()){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		if(!$identity = $user->getIdentity()){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		if(!$u = $this->users->find($identity->overUser)){
			$this->flashDanger('Uživatel nenalezen.');
			$this->goToHomePage();
		}

		$user->login($this->facIdentity->create($u));
		$this->goToHomePage();
	}

	public function actionSwitch(
		$id)
	{
		if(!$id){
			$this->flashDanger('Uživatel neuveden.');
			$this->goToHomePage();
		}

		if(!$user = $this->getUser()){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		if(!$user->isInRole('super') || !$user->isAllowed('User', 'relog')){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		if(!$u = $this->users->find($id)){
			$this->flashDanger('Uživatel nenalezen.');
			$this->goToHomePage();
		}

		$oldId = $user->id;
		$user->login($this->facIdentity->create($u));
		$user->setOverUser($oldId);

		$this->goToHomePage();
	}

	public function actionChangeRole(
		$backlink,
		$role)
	{
		if(!$user = $this->getUser()){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		if(!$this->user->isInRole('super') && !$this->user->isAllowed('Role', 'change')){
			throw new AuthenticationException('Nemáte oprávnění', 410);
		}

		$user->setOverRole($role);

		if($backlink && $this->checkStoredRequest($backlink)){
			$this->restoreRequest($backlink);
		}

		$this->goToHomePage();
	}
}