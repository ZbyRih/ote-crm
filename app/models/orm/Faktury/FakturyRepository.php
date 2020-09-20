<?php


namespace App\Models\Orm\Faktury;

use Nextras\Orm\Repository\Repository;

/**
 * @method FakturaEntity getById($id)
 */
class FakturyRepository extends Repository
{

	public static function getEntityClassNames()
	{
		return [
			FakturaEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return FakturaEntity|NULL
	 */
	public function getById(
		$id
	) {
		return parent::getById($id);
	}
}
