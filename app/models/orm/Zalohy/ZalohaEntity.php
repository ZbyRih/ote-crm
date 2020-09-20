<?php

namespace App\Models\Orm\Zalohy;

use Nextras\Orm\Entity\Entity;

/**
 *

 * @property mixed $id {primary-proxy}
 * @property int $zalohaId {primary}
 * @property int $odberMistId
 * @property int $klientId
 * @property \DateTime $od
 * @property \DateTime $do
 * @property float|NULL $vyse {default 0.0}
 * @property float|NULL $uhrazeno {default 0.0}
 * @property float|NULL $preplatek {default 0.0}
 * @property bool $uhr {default 0}
 * @property \DateTime|NULL $dne
 * @property string|NULL $vs
 *
 */
class ZalohaEntity extends Entity{
}