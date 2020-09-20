<?php

namespace App\Models\Orm\Platby;

use Nextras\Orm\Repository\Repository;

class PlatbyRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			PlatbaEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return PlatbaEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}