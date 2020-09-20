<?php

namespace App\Models\Orm\Settings;

use Nextras\Orm\Entity\Entity;
use App\Extensions\Abstracts\TArrayAccessOrmEntity;

/**
 * @property int $id {primary}
 * @property string $key
 * @property string|NULL $value {default null}
 * @property string $type {enum self::TYPE_*}
 * @property string|NULL $description
 * @property string $group
 * @property bool $require
 */
class SettingEntity extends Entity implements \ArrayAccess{
	use TArrayAccessOrmEntity;

	const TYPE_INT = 'integer';

	const TYPE_FLOAT = 'float';

	const TYPE_STRING = 'string';

	const TYPE_WSWG = 'wswg';

	const TYPE_FILE = 'file';

	const TYPE_DATE = 'date';
}

// Field        Type          Collation        Null    Key     Default  Extra           Privileges                       Comment
// -----------  ------------  ---------------  ------  ------  -------  --------------  -------------------------------  ---------
// id           int(11)       (NULL)           NO      PRI     (NULL)   auto_increment  select,insert,update,references
// key          varchar(50)   utf8_general_ci  NO              (NULL)                   select,insert,update,references
// value        varchar(100)  utf8_general_ci  NO              (NULL)                   select,insert,update,references
// type         varchar(10)   utf8_general_ci  NO              (NULL)                   select,insert,update,references
// require      tinyint(1)    (NULL)           NO              0                        select,insert,update,references
// description  varchar(255)  utf8_general_ci  YES             (NULL)                   select,insert,update,references
// group        varchar(5)    utf8_general_ci  NO              main                     select,insert,update,references