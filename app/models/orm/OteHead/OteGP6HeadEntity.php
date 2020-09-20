<?php

namespace App\Models\Orm\OteGP6Head;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property int|NULL $odberMistId
 * @property int|NULL $fakturaId
 * @property bool $depricated
 * @property bool $cancelled
 * @property string $oteId
 * @property string $pofId
 * @property string $type
 * @property string $version
 * @property string $subjectsOpm
 * @property float $priceTotal
 * @property float $priceTotalDph
 * @property DateTime|NULL $from
 * @property DateTime|NULL $to
 * @property float|NULL $yearReCalculatedValue
 * @property string|NULL $attributesSegment
 * @property string|NULL $attributesNumber
 * @property string|NULL $attributesAnumber
 * @property string|NULL $attributesCorReason
 * @property string|NULL $attributesComplId
 * @property string|NULL $attributesSCNumber
 */
class OteGP6HeadEntity extends Entity{
}
