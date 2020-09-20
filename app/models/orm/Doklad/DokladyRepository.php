<?php

namespace App\Models\Orm\Doklad;

use Nextras\Orm\Repository\Repository;

class DokladyRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			DokladEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return DokladEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}