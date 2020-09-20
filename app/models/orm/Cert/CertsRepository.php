<?php

namespace App\Models\Orm\Cert;

use Nextras\Orm\Repository\Repository;

/**
 * @method CertEntity|NULL getById($id)
 */
class CertsRepository extends Repository{

	public static function getEntityClassNames()
	{
		return [
			CertEntity::class
		];
	}
}