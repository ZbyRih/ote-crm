<?php

namespace App\Models\Orm\OdberMists;

use Nextras\Orm\Entity\Entity;
use App\Models\Orm\Address\AddressEntity;

/**
 * @property int $id {primary-proxy}
 * @property int $odberMistId {primary}
 * @property int $distId {default 0}
 * @property \DateTime $createdate
 * @property bool $depricated {default false}
 * @property string|NULL $com
 * @property string|NULL $eic
 * @property string|NULL $popis
 * @property int $ownerId
 * @property int $createdBy
 * @property AddressEntity $addressId {1:1 AddressEntity, isMain=true, oneSided=true}
 */
class OdberMistEntity extends Entity{

	public function getAsInfo()
	{
		return $this->com . ' (' . $this->eic . ')';
	}
}

// Field          Type                  Collation      Null    Key     Default  Extra           Privileges                       Comment
// -------------  --------------------  -------------  ------  ------  -------  --------------  -------------------------------  ---------
// odber_mist_id  bigint(20) unsigned   (NULL)         NO      PRI     (NULL)   auto_increment  select,insert,update,references
// com            varchar(30)           utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// eic            varchar(30)           utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// address_id     bigint(20) unsigned   (NULL)         NO      MUL     (NULL)                   select,insert,update,references
// popis          varchar(255)          utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// dist_id        smallint(2) unsigned  (NULL)         NO              0                        select,insert,update,references
// deprecated     tinyint(1)            (NULL)         YES             0                        select,insert,update,references
// owner_id       int(11) unsigned      (NULL)         NO              (NULL)                   select,insert,update,references
// created_by     int(11) unsigned      (NULL)         NO              (NULL)                   select,insert,update,references
// createdate     datetime              (NULL)         NO              (NULL)                   select,insert,update,references
