<?php

namespace App\Extensions\App\User;

use App\Models\Repositories\RoleRepository;
use App\Models\Tables\UserTable;
use Nette\Security\Permission;

class RoleFinder{

	/** @var Authorizator */
	private $acl;

	/** @var UserTable */
	private $users;

	public function __construct(RoleRepository $roles, UserTable $users){
		$this->users = $users;
		$this->acl = new Authorizator($roles);

		$this->acl->allow('super', Permission::ALL);

		$roles->getAll()->each(function ($v, $k){
			if($v->role != 'super'){
				$this->acl->setRoleRules($v->role, $v->denorm);
			}
		});
	}

	public function getUsersByAllowedBy($resource, $privilege = Permission::ALL){
		if($roles = $this->getAllRolesFor($resource, $privilege)){
			$s = $this->users->table();
			return $s->where('role', $roles)->fetchPairs('id', 'login');
		}
		return [];
	}

	public function getAllRolesFor($resource, $privilege = Permission::ALL){
		$ret = [];

		$a = explode('_', $resource);
		if(count($a) > 1){
			$resource = $a[0];
			$privilege = $a[1];
		}

		foreach($this->acl->getRoles() as $r){
			if(Authorizator::_isAllowed(function ($resource, $privilege) use ($r){
				return $this->acl->isAllowed($r, $resource, $privilege);
			}, $resource, $privilege)){
				$ret[] = $r;
			}
		}
		return !empty($ret) ? $ret : null;
	}
}