<?php

namespace App\Models\Orm\PlatbyZarazeni;

use Nextras\Orm\Entity\Entity;
use App\Models\Orm\Platby\PlatbaEntity;
use Nextras\Orm\Relationships\HasMany;

/**
 * @property int $id {primary}
 * @property int $klientId
 * @property int|null $fakskupId {default null}
 * @property int|null $omId {default null}
 * @property HasMany|PlatbaEntity $platba {1:1 PlatbaEntity::$zarazeni, isMain = true}
 */
class PlatbaZarazeniEntity extends Entity{
}