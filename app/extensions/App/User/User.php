<?php

namespace App\Extensions\App\User;

use Nette\Security\IAuthorizator;

class IdentityNotSetException extends \Exception{
}

/**
 * @property-read UserIdentity $identity
 *
 */
class User extends \Nette\Security\User{

	public function setOverRole(
		$role)
	{
		if(!$this->identity){
			throw new IdentityNotSetException();
		}
		$this->identity->overRole = $role == $this->identity->role ? null : $role;
	}

	public function setOverUser(
		$userId)
	{
		if(!$this->identity){
			throw new IdentityNotSetException();
		}
		$this->identity->overUser = $userId == $this->identity->id ? null : $userId;
	}

	public function isSuper()
	{
		return $this->isInRole('super');
	}

	public function isInRole(
		$role)
	{
		if(!$this->identity){
			throw new IdentityNotSetException();
		}
		if($this->identity->role === 'super' && $role === 'super'){
			return true;
		}
		return parent::isInRole($role);
	}

	public function isInRoles(
		$roles)
	{
		return !empty(array_intersect($this->getRoles(), is_array($roles) ? $roles : [
			$roles
		]));
	}

	/**
	 * {@inheritdoc}
	 * @see \Nette\Security\User::getRoles()
	 */
	public function getRoles()
	{
		if(!$this->identity){
			throw new IdentityNotSetException();
		}
		$roles = parent::getRoles();
		if($this->identity->role == 'super'){
			array_unshift($roles, 'super');
		}
		return $roles;
	}

	public function isAllowed(
		$resource = IAuthorizator::ALL,
		$privilege = IAuthorizator::ALL)
	{
		$a = explode('_', $resource);
		if(count($a) > 1){
			$resource = $a[0];
			$privilege = $a[1];
		}
		return Authorizator::_isAllowed(function (
			$resource,
			$privilege)
		{
			return $this->pIsAllowed($resource, $privilege);
		}, $resource, $privilege);
	}

	public function pIsAllowed(
		$resource,
		$privilege)
	{
		return parent::isAllowed($resource, $privilege);
	}

	/**
	 * @return UserIdentity
	 */
	public function getIdentity()
	{
		return parent::getIdentity();
	}
}