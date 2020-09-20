<?php

namespace App\Models\Orm\Address;

use Nextras\Orm\Repository\Repository;

class AddressRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			AddressEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return AddressEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}