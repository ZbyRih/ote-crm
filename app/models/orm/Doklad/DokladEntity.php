<?php

namespace App\Models\Orm\Doklad;

use Nextras\Orm\Entity\Entity;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Platby\PlatbaEntity;
use Nextras\Orm\Relationships\OneHasOne;

/**
 * @property int $id {primary}
 * @property \DateTime $created {default now}
 * @property string $cislo
 * @property float $platba
 * @property \DateTime $denZdanPln {default now}
 * @property OneHasOne|PlatbaEntity $platbaId {1:1 PlatbaEntity::$doklad, isMain = true}
 */
class DokladEntity extends Entity{
}