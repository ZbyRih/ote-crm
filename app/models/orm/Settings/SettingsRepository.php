<?php

namespace App\Models\Orm\Settings;

use Nextras\Orm\Repository\Repository;

class SettingsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			SettingEntity::class
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nextras\Orm\Repository\Repository::getById()
	 * @return SettingEntity|NULL
	 */
	public function getById(
		$id)
	{
		return parent::getById($id);
	}
}