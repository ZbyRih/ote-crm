<?php

namespace Tests\Utils;

use Nette\Security\Identity;

class IdentityBuilder{

	/** @var int */
	private $userId = 178;

	/** @var string  */
	private $role = 'super';

	/** @var string */
	private $home = '';

	/** @var bool */
	private $notifikace = false;

	/** @var bool */
	private $allowObj = false;

	/** @var [] */
	private $rPerms = [];

	/** @var [] */
	private $uPerms = [];

	/**
	 *
	 * @param number $userId
	 */
	public function setUserId(
		$userId)
	{
		$this->userId = $userId;
	}

	/**
	 *
	 * @param string $role
	 */
	public function setRole(
		$role)
	{
		$this->role = $role;
	}

	/**
	 *
	 * @param string $home
	 */
	public function setHome(
		$home)
	{
		$this->home = $home;
	}

	/**
	 *
	 * @param boolean $notifikace
	 */
	public function setNotifikace(
		$notifikace)
	{
		$this->notifikace = $notifikace;
	}

	/**
	 *
	 * @param boolean $allowObj
	 */
	public function setAllowObj(
		$allowObj)
	{
		$this->allowObj = $allowObj;
	}

	/**
	 * denormalizovany pole ['res' => '', 'perm' => '']
	 * @param [] $rPerms
	 */
	public function setRPerms(
		$rPerms)
	{
		$this->rPerms = $rPerms;
	}

	/**
	 * denormalizovany pole ['res' => '', 'perm' => '']
	 * @param [] $uPerms
	 */
	public function setUPerms(
		$uPerms)
	{
		$this->uPerms = $uPerms;
	}

	public function create()
	{
		return new Identity($this->userId, $this->role,
			[
				'overUser' => null,
				'overRole' => null,
				'overView' => null,
				'name' => 'test',
				'login' => 'test',
				'home' => $this->home,
				'role' => $this->role,
				'email' => 'test@localhost',
				'notify' => $this->notifikace,
				'allowObj' => $this->allowObj,
				'rPerms' => $this->rPerms,
				'uPerms' => $this->uPerms
			]);
	}
}