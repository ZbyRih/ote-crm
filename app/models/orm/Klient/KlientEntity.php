<?php

namespace App\Models\Orm\Klients;

use Nextras\Orm\Entity\Entity;
use App\Extensions\Abstracts\TArrayAccessOrmEntity;
use App\Models\Orm\Address\AddressEntity;
use App\Models\Orm\KlientDetails\KlientDetailEntity;

/**
 * @property int $id {primary-proxy}
 * @property int $klientId {primary}
 * @property bool $deleted {default false}
 * @property bool $active {default false}
 * @property bool $disabled {default false}
 * @property \DateTime $createdate
 * @property float $discount {default 0.0}
 * @property string|NULL $tags
 * @property int $ownerId
 * @property int $createdBy
 * @property bool $fakturacni {default false}
 * @property KlientDetailEntity $klientDetailId {1:1 KlientDetailEntity, isMain=true, oneSided=true}
 * @property AddressEntity $addressId {1:1 AddressEntity, isMain=true, oneSided=true}
 * @property AddressEntity $korespondId|NULL {1:1 AddressEntity, isMain=true, oneSided=true}
 */
class KlientEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;

	public function getKontaktni()
	{
		if($this->hasValue('korespondId') && !$this->korespondId->isEmpty()){
			return $this->korespondId;
		}

		return $this->addressId;
	}

	public function getFakturacni()
	{
		if($this->addressId){
			return $this->addressId;
		}
		return null;
	}
}