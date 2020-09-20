<?php

namespace App\Models\Orm\Cert;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property string $file
 * @property string $hash
 * @property \DateTime $validTo
 * @property \DateTime $created {default now}
 */
class CertEntity extends Entity{
}