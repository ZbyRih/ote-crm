<?php

namespace App\Extensions\App\User;

use App\Models\Repositories\RoleRepository;

class Authorizator extends \Nette\Security\Permission{

	public function __construct(RoleRepository $roles){
		foreach($roles->getRoles() as $r){
			if(!$this->hasRole($r)){
				$this->addRole($r);
			}
		}
	}

	public static function _isAllowed($autorizator, $resource, $privilege){
		if($privilege == 'edit'){
			return $autorizator($resource, $privilege) || $autorizator($resource, 'delete');
		}else if($privilege == 'add'){
			return $autorizator($resource, $privilege) || $autorizator($resource, 'delete') || $autorizator($resource, 'edit');
		}else if($privilege == 'view'){
			return $autorizator($resource, $privilege) || $autorizator($resource, 'delete') || $autorizator($resource, 'edit') || $autorizator($resource, 'add');
		}
		return $autorizator($resource, $privilege);
	}

	public function setRoleRules($role, $rules){
		$role = is_array($role) ? reset($role) : $role;

		if($role == 'super'){
			$this->allow('super');
			return;
		}

		foreach($rules as $p){
			if($p['allow']){
				$this->allow($role, $p['res'], $p['perm']);
			}else{
				$this->deny($role, $p['res'], $p['perm']);
			}
		}
	}
}