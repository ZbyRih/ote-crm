<?php

namespace App\Models\Orm\OteMessages;

use Nextras\Orm\Repository\Repository;

class OteMessagesRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			OteMessageEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return OteMessageEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}