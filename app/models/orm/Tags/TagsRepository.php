<?php

namespace App\Models\Orm\Tags;

use Nextras\Orm\Repository\Repository;

class TagsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			TagEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return TagEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}