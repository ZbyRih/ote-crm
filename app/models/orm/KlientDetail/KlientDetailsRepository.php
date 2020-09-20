<?php

namespace App\Models\Orm\KlientDetails;

use Nextras\Orm\Repository\Repository;

class KlientDetailsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			KlientDetailEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return KlientDetailEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}