<?php

namespace App\Models\Orm\PlatbyParZalohy;

use Nextras\Orm\Repository\Repository;

class PlatbyParZalohyRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			PlatbaParZalohaEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return PlatbaParZalohaEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}