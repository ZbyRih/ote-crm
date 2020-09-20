<?php

namespace App\Models\Orm\Users;

use Nextras\Orm\Repository\Repository;

class UsersRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			UserEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return UserEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}