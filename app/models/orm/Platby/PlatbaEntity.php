<?php

namespace App\Models\Orm\Platby;

use App\Extensions\Abstracts\TArrayAccessOrmEntity;
use App\Models\Orm\Doklad\DokladEntity;
use App\Models\Orm\PlatbyZarazeni\PlatbaZarazeniEntity;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\HasOne;

/**
 * @property mixed $id {primary-proxy}
 * @property string $uuid {virtual}
 * @property int $platbaId {primary}
 * @property string|NULL $type
 * @property \DateTime|NULL $when
 * @property string|NULL $fromCu
 * @property string|NULL $subject
 * @property float|NULL $platba {default 0.0}
 * @property float|NULL $preplatek {default 0.0}
 * @property string|NULL $vs
 * @property string|NULL $ks
 * @property string|NULL $ss
 * @property string|NULL $msg
 * @property string $dphCoef {default 0}
 * @property bool $man {default 0}
 * @property bool $edit {default 0}
 * @property HasOne|DokladEntity|NULL $doklad {1:1 DokladEntity::$platbaId}
 * @property HasOne|PlatbaZarazeniEntity|NULL $zarazeni {1:1 PlatbaZarazeniEntity::$platba}
 */
class PlatbaEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;

	public static function index(
		PlatbaEntity $v)
	{
		return $v->vs . '-' . $v->fromCu . '-' . $v->when->format('dmY') . '-' . $v->platba;
	}

	public function hasDoklad()
	{
		return $this->hasValue('doklad');
	}

	public function hasZarazeni()
	{
		return $this->hasValue('zarazeni');
	}
}