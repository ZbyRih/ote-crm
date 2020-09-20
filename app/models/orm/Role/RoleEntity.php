<?php

namespace App\Models\Orm\Roles;

use Nextras\Orm\Entity\Entity;

/**
 * @property int $id {primary}
 * @property string $role
 * @property string $nazev
 * @property string $home
 * @property DateTime|NULL $deleted
 * @property boolean $super
 * @property string $perms
 */
class RoleEntity extends Entity{
}

// Field    Type         Collation        Null    Key     Default  Extra           Privileges                       Comment
// -------  -----------  ---------------  ------  ------  -------  --------------  -------------------------------  ---------
// id       int(11)      (NULL)           NO      PRI     (NULL)   auto_increment  select,insert,update,references
// role     varchar(20)  utf8_general_ci  NO              (NULL)                   select,insert,update,references
// nazev    varchar(30)  utf8_general_ci  NO              (NULL)                   select,insert,update,references
// home     varchar(30)  utf8_general_ci  NO              (NULL)                   select,insert,update,references
// deleted  datetime     (NULL)           YES             (NULL)                   select,insert,update,references
// super    tinyint(1)   (NULL)           NO              0                        select,insert,update,references
// perms    json         (NULL)           NO              (NULL)                   select,insert,update,references