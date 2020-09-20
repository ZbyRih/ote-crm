<?php

namespace App\Models\Orm\OteGP6Body;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property int|NULL $headId {1:1 \App\Models\Orm\OteGP6Head\OteGP6HeadEntity, isMain=true, oneSided=true}
 * @property int|NULL $odberMistId
 * @property string $oteId
 * @property string $data
 * @property string $type {virtual}
 */
class OteGP6BodyEntity extends Entity{
}