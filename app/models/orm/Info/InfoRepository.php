<?php

namespace App\Models\Orm\Info;

use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Repository\Repository;

class InfoRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			InfoEntity::class
		];
	}

	/**
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return InfoEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}

	/**
	 * @param string $type
	 * @return InfoEntity|NULL
	 */
	public function getLastByType(
		$type)
	{
		return $this->findBy([
			'type' => $type
		])
			->orderBy('created', ICollection::DESC)
			->limitBy(1)
			->fetch();
	}
}