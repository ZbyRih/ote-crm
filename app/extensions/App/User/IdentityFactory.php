<?php
namespace App\Extensions\App\User;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Repositories\RoleRepository;
use App\Models\Tables\RoleTable;
use App\Models\Tables\UserTable;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;

class IdentityFactory{

	/** @var UserTable */
	private $tblUser;

	/** @var RoleRepository */
	private $roles;

	function __construct(UserTable $uzivatel, RoleRepository $roles, Authorizator $author){
		$this->tblUser = $uzivatel;
		$this->roles = $roles;
	}

	/**
	 *
	 * @param ArrayHash $params
	 * @throws AuthenticationException
	 * @return UserIdentity
	 */
	public function create($params){
		$params = $params instanceof Identity ? $params->getData() + [
			'id' => $params->id
		] : $params;
		$params = is_array($params) ? ArrayHash::from($params) : $params;

		if(!$user = $this->tblUser->find($params->id)){
			throw new AuthenticationException('Uživatel nenalezen.');
		}

		if($user->deleted){
			throw new AuthenticationException('Neplatný uživatel.');
		}

		$uRole = $params->offsetExists('overRole') ? $params->overRole : $user->role;

		if(!$role = $this->roles->get($uRole)){
			throw new AuthenticationException('Role nenalezena.');
		}

		$rPerms = $role->denorm;

		$uPrava = $user->perms;
		if($params->offsetExists('overUser')){
			if($overUser = $this->tblUser->find($params->overUser)){
				$uPrava = $overUser->perms;
			}
		}
		$uPerms = RoleTable::permsDenorm(RoleTable::permsUnpack($uPrava));

		return new UserIdentity($user->id, $uRole,
			[
				'overUser' => $params->offsetExists('overUser') ? $params->overUser : null,
				'overRole' => $params->offsetExists('overRole') ? $params->overRole : null,
				'name' => $user->jmeno,
				'login' => $user->login,
				'role' => $user->role,
				'email' => $user->login,
				'home' => $role->home,
				'rPerms' => $rPerms,
				'uPerms' => $uPerms,
				'row' => $user->toArray()
			]);
	}
}