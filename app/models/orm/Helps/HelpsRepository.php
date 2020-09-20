<?php

namespace App\Models\Orm\Helps;

use Nextras\Orm\Repository\Repository;

class HelpsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			HelpEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return HelpEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}