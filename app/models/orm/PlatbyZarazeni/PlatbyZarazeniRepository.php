<?php

namespace App\Models\Orm\PlatbyZarazeni;

use Nextras\Orm\Repository\Repository;

class PlatbyZarazeniRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			PlatbaZarazeniEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return PlatbaZarazeniEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}