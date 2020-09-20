<?php

namespace App\Models\Orm\Zalohy;

use Nextras\Orm\Repository\Repository;

class ZalohyRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			ZalohaEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return ZalohaEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}