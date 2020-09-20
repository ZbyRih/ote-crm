<?php

namespace App\Models\Orm\Klients;

use Nextras\Orm\Repository\Repository;

class KlientsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			KlientEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return KlientEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}