<?php

namespace App\Models\Orm\Faktury;

use App\Extensions\Abstracts\TArrayAccessOrmEntity;
use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property int|NULL $klientId
 * @property int|NULL $omId
 * @property int $userId
 * @property int|NULL $platbaId
 * @property bool $upraveno {default 0}
 * @property bool $man {default 0}
 * @property bool $storno {default 0}
 * @property \DateTime|NULL $deleted
 * @property string $cis
 * @property \DateTime|NULL $od
 * @property \DateTime|NULL $do
 * @property \DateTime|NULL $vystaveno
 * @property \DateTime|NULL $odeslano
 * @property \DateTime|NULL $splatnost
 * @property float $suma
 * @property float $dph
 * @property float $sumaADph
 * @property float $sumZals
 * @property float $preplatek
 * @property \DateTime|NULL $dzp
 * @property float $dphSazba
 * @property float $dphKoef
 * @property float $danZp
 * @property float $cenaDistribuce
 * @property float $cenaDanPlyn
 * @property float $cenaZaMwh
 * @property float $nakupMwh
 * @property float $spotreba
 * @property float $uhrazeno
 * @property \DateTime|NULL $uhrazenoDne
 * @property \DateTime|NULL $uhrazenoDzp
 * @property string $html
 * @property string $params
 * @property string $ext
 */
class FakturaEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;
}
