<?php

namespace App\Components;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Utils\Arrays;
use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Repositories\RoleRepository;

class NavBar extends BaseComponent{

	/** @var RoleRepository */
	private $roles;

	/** @var IComponentNavBarNotificationFactory */
	private $comNotif;

	public function __construct(
		RoleRepository $roles,
		IComponentNavBarNotificationFactory $comNotif)
	{
		$this->roles = $roles;
		$this->comNotif = $comNotif;
	}

	public function render()
	{
		$identity = $this->user->getIdentity();
		$firstRole = Arrays::first($identity->getRoles());
		$isSuper = $this->user->isInRole('super');

		$roles = $this->roles->getNames($isSuper ? [] : [
			'super'
		], true);

		$t = $this->template;
		$t->allowRelog = ($isSuper || $this->user->isAllowed('Role', 'change'));

		$t->rolesOptions = collection($roles)->map(
			function (
				$v,
				$k) use (
			$firstRole){
				return ArrayHash::from([
					'name' => $v,
					'selected' => ($k == $firstRole)
				]);
			})->toArray();

		$t->selectedRole = $roles[$firstRole];

		$t->identity = $identity;
		$t->resource = $this->presenter->getResource();
		$t->settings = $this->presenter->settings;
		$t->backlink = $this->presenter->storeRequest();

		parent::render();
	}

	public function createComponentNotification()
	{
		$com = $this->comNotif->create();
		$com->setUserId($this->user->id);
		return $com;
	}

	public function handleSwapRole(
		$backlink,
		$role)
	{
		if(!$this->user->isInRole('super') && !$this->user->isAllowed('Role', 'change')){
			throw new \Nette\Security\AuthenticationException('Nemáte oprávnění', 410);
		}

		$this->user->setOverRole($role);

		if($this->presenter->checkStoredRequest($backlink)){
			$this->presenter->restoreRequest($backlink);
		}

		$this->presenter->redirect(":Homepage:");
	}

	public function handleBack()
	{
		$i = $this->user->getIdentity();

		if(!$u = $this->usrTbl->find($i->overUser)){
			$this->flashDanger('Uživatel nenalezen.');
			$this->presenter->redirect(":Homepage:");
		}

		$this->user->login($this->identityFactory->create([
			'id' => $u->id,
			'overUser' => null,
			'overRole' => null
		]));

		$this->presenter->redirect(":Homepage:");
	}
}