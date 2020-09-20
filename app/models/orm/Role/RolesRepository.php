<?php

namespace App\Models\Orm\Roles;

use Nextras\Orm\Repository\Repository;

class RolesRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			RoleEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return RoleEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}