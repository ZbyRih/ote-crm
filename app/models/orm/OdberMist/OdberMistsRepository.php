<?php

namespace App\Models\Orm\OdberMists;

use Nextras\Orm\Repository\Repository;

class OdberMistRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			OdberMistEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return OdberMistEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}