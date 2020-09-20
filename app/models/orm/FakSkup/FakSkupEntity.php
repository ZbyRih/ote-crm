<?php

namespace App\Models\Orm\FakSkups;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary-proxy}
 * @property int $fakSkupId {primary}
 * @property int $faKlientId
 * @property int|NULL $klientId
 * @property int $typ {default 0}
 * @property string|NULL $cis
 * @property string|NULL $nazev
 * @property int $ownerId
 */
class FakSkupEntity extends Entity{
}

// Field         Type                 Collation      Null    Key     Default  Extra           Privileges                       Comment
// ------------  -------------------  -------------  ------  ------  -------  --------------  -------------------------------  ---------
// fak_skup_id   bigint(20) unsigned  (NULL)         NO      PRI     (NULL)   auto_increment  select,insert,update,references
// fa_klient_id  bigint(20) unsigned  (NULL)         NO              (NULL)                   select,insert,update,references
// klient_id     bigint(20) unsigned  (NULL)         NO              (NULL)                   select,insert,update,references
// typ           tinyint(1) unsigned  (NULL)         NO              0                        select,insert,update,references
// cis           varchar(40)          utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// nazev         varchar(40)          utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// owner_id      int(11) unsigned     (NULL)         NO              (NULL)                   select,insert,update,references
