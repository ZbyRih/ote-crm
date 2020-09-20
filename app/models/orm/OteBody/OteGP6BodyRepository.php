<?php

namespace App\Models\Orm\OteGP6Body;

use Nextras\Orm\Repository\Repository;

class OteGP6BodyRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			OteGP6BodyEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return OteGP6BodyEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}