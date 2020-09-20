<?php

namespace App\Models\Orm\TagsToObjects;

use Nextras\Orm\Repository\Repository;

class TagsToObjectsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			TagToObjectEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return TagToObjectEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}