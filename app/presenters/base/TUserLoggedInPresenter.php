<?php

namespace App\Presenters;

use App\Extensions\App\User\IdentityFactory;
use App\Extensions\App\User\User;
use App\Extensions\App\User\UserIdentity;
use App\Models\Repositories\UserRepository;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthorizator;
use Nette\Application\ApplicationException;
use Nette\InvalidStateException;

class UnableInitiateUserException extends \Exception{
}

trait TUserLoggedInPresenter{

	/**
	 *
	 * @param string $resource
	 * @param Container $container
	 * @return void
	 */
	protected function userInitiate($resource, Container $container){
		$this->checkLogin($resource);
		$this->authorizate($container);
		$this->checkResourceAccess($resource);
	}

	/**
	 *
	 * @param string $resource
	 * @return void
	 * @throws UnableInitiateUserException
	 */
	private function checkLogin($resource){
		if(!$this->isLogged() && $resource != 'Sign'){
			throw new UnableInitiateUserException();
		}
	}

	/**
	 *
	 * @param Container $container
	 * @return void
	 * @throws ApplicationException
	 * @throws UnableInitiateUserException
	 */
	private function authorizate(Container $container){
		if(!$this->isLogged()){
			return;
		}

		$auth = $container->getByType(IAuthorizator::class);
		$if = $container->getByType(IdentityFactory::class);
		$ur = $container->getByType(UserRepository::class);

		if(!$auth || !$if || !$ur){
			throw new ApplicationException('Pro inicializaci uzivatele nebyli zízkány všechny závislosti');
		}

		try{
			if(!$user = $this->getUser()){
				throw new ApplicationException('Metoda getUser nevrátila uživatele');
			}

			$user->getStorage()->setIdentity($newIdentity = $if->create($this->getIdentity()));

			$roles = $user->getRoles();

			$auth->setRoleRules($roles, $newIdentity->rPerms);
			$auth->setRoleRules($roles, $newIdentity->uPerms);
			$ur->updateLast($newIdentity->row);
		}catch(AuthenticationException $e){
			$user->logout(true);
			$this->flashDanger($e->getMessage());
			throw new UnableInitiateUserException();
		}
	}

	/**
	 *
	 * @param string $resource
	 * @return void
	 */
	private function checkResourceAccess($resource){
		if($resource == 'Sign' || $this->isAllowed('view')){
			return;
		}

		if(!$identity = $this->getIdentity()){
			throw new InvalidStateException();
		}

		if(!$home = $identity->home){
			return;
		}

		if($home == $this->getName()){
			$this->redirect(':Denied:');
		}else{
			$this->redirect($home . ':');
		}
	}

	/**
	 *
	 * @param bool|string|null $privilege
	 * @return boolean
	 */
	public function isAllowed($privilege = IAuthorizator::ALL){
		if(!$user = $this->getUser()){
			return false;
		}
		return $user->isAllowed($this->getResource(), $privilege);
	}

	/**
	 *
	 * @param string $role
	 * @return boolean
	 */
	public function isInRole($role){
		if(!$user = $this->getUser()){
			return false;
		}
		return $user->isInRole($role);
	}

	/**
	 *
	 * @return boolean
	 */
	public function isLogged(){
		if(!$user = $this->getUser()){
			return false;
		}
		return $user->isLoggedIn();
	}

	/**
	 *
	 * @return UserIdentity|NULL
	 */
	public function getIdentity(){
		if(!$user = $this->getUser()){
			return null;
		}
		return $user->getIdentity();
	}

	/**
	 *
	 * @return User
	 * @throws \Nette\InvalidStateException
	 */
	public function getUser(){
		return parent::getUser();
	}
}