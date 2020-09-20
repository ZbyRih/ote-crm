<?php

namespace App\Models\Orm\Address;

use Nextras\Orm\Entity\Entity;
use App\Extensions\Abstracts\TArrayAccessOrmEntity;

/**
 * @property int $id {primary-proxy}
 * @property int $addressId {primary}
 * @property string|NULL $city
 * @property string|NULL $street
 * @property string|NULL $cp
 * @property string|NULL $co
 * @property string|NULL $zip
 */
class AddressEntity extends Entity implements \ArrayAccess{

	use TArrayAccessOrmEntity;

	public function isEmpty()
	{
		if(empty(trim($this->city))){
			return true;
		}

		if(empty(trim($this->street))){
			return true;
		}

		return false;
	}

	public function getUlCpCo()
	{
		return $this->street . $this->getCpOp();
	}

	public function getCpOp()
	{
		$cpo = $this->cp;

		if(!empty($this->co)){
			$cpo = $cpo . (!empty($cpo) ? '/' : '') . $this->co;
		}

		return (empty($cpo) ? '' : ' ') . $cpo;
	}
}