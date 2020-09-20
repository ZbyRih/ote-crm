<?php

namespace App\Models\Orm\Users;

use Nextras\Orm\Entity\Entity;
use App\Models\Orm\UuidProperty;
use Ramsey\Uuid\Uuid;

/**
 *

 * @property int $id {primary}
 * @property string $login
 * @property string $pass
 * @property string $jmeno
 * @property string $role
 * @property string $email
 * @property DateTime|NULL $activity
 * @property string $lastloc
 * @property string $perms
 * @property DateTime|NULL $deleted
 * @property boolean $delegate
 * @property boolean $onlyown
 * @property boolean $theme
 * @property boolean $platby
 * @property boolean $chowner
 * @property boolean $info_ote
 * @property boolean $info_banka
 */
class UserEntity extends Entity{
}

// id          int(11) unsigned     (NULL)           NO      PRI     (NULL)   auto_increment  select,insert,update,references
// login       varchar(50)          utf8_czech_ci    YES             (NULL)                   select,insert,update,references
// pass        varchar(255)         utf8_czech_ci    YES             (NULL)                   select,insert,update,references
// jmeno       varchar(60)          utf8_czech_ci    YES             (NULL)                   select,insert,update,references
// role        varchar(40)          utf8_general_ci  YES             (NULL)                   select,insert,update,references
// email       varchar(255)         utf8_general_ci  YES             (NULL)                   select,insert,update,references
// activity    datetime             (NULL)           YES             (NULL)                   select,insert,update,references
// lastloc     text                 utf8_general_ci  YES             (NULL)                   select,insert,update,references
// perms       text                 utf8_czech_ci    YES             (NULL)                   select,insert,update,references
// deleted     datetime             (NULL)           YES             (NULL)                   select,insert,update,references

//
// delegate    tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// onlyown     tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// theme       tinyint(1) unsigned  (NULL)           YES             0                        select,insert,update,references
// platby      tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// chowner     tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references

// tohle budou v budoucnu parametry
// info_ote    tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
// info_banka  tinyint(1) unsigned  (NULL)           NO              0                        select,insert,update,references
