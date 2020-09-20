<?php

namespace App\Models\Orm\SmlOMs;

use Nextras\Orm\Entity\Entity;
use App\Extensions\Abstracts\TArrayAccessOrmEntity;
use App\Models\Orm\SmlOMFlags\SmlOMFlagEntity;
use App\Models\Enums\SmlOMEnums;

/**

 * @property int $id {primary}
 * @property int $klientId
 * @property int $odberMistId
 * @property int|NULL $fakSkupId
 * @property SmlOMFlagEntity $flagsId {1:1 SmlOMFlagEntity, isMain=true, oneSided=true}
 * @property int|NULL $typSml {default 0}
 * @property int|NULL $category {default 0}
 * @property \DateTime $od
 * @property \DateTime $do
 * @property int|NULL $vztah {default 0}
 * @property int|NULL $interval {default 0}{enum SmlOMEnums::INTERVAL_*}
 * @property string|NULL $vs
 * @property float|NULL $zaloha {default 0.0}
 * @property float|NULL $cena_mwh {default 0.0}
 */
class SmlOMEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;
}