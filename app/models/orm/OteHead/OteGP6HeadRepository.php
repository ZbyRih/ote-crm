<?php

namespace App\Models\Orm\OteGP6Head;

use Nextras\Orm\Repository\Repository;

class OteGP6HeadRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			OteGP6HeadEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return OteGP6HeadEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}