<?php

namespace App\Models\Orm\FakSkups;

use Nextras\Orm\Repository\Repository;

class FakSkupsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			FakSkupEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return FakSkupEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}