<?php

namespace App\Models\Orm\Info;

use Nextras\Orm\Entity\Entity;
use App\Models\Enums\InfoEnums;

/**
 * @property int $id {primary}
 * @property \DateTime $created {default now}
 * @property string $data
 * @property string $type {enum InfoEnums::TYPE_*}
 */
class InfoEntity extends Entity{
}