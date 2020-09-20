<?php

namespace App\Extensions\App\User;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Repositories\UserRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;

class Authenticator implements \Nette\Security\IAuthenticator{

	/** @var UserRepository */
	private $repUser;

	/** @var IdentityFactory */
	private $identityFactory;

	public function __construct(UserRepository $repUser, IdentityFactory $identityFactory){
		$this->repUser = $repUser;
		$this->identityFactory = $identityFactory;
	}

	public function authenticate(array $credentials){
		list($username, $password) = $credentials;

		if(!($user = $this->repUser->findByLogin($username))){
			throw new AuthenticationException('Neexistující login.');
		}

		if($user->deleted){
			throw new AuthenticationException('Neplatný login.');
		}

		if(!Passwords::verify($password, $user->pass)){
			throw new AuthenticationException('Neplatné přihlášení.');
		}

		return $this->identityFactory->create(ArrayHash::from([
			'id' => $user->id,
			'overUser' => null,
			'overRole' => null
		]));
	}
}