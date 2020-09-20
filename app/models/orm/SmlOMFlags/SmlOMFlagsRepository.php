<?php

namespace App\Models\Orm\SmlOMFlags;

use Nextras\Orm\Repository\Repository;

class SmlOMFlagsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			SmlOMFlagEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return SmlOMFlagEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}