<?php

namespace App\Models\Orm\Tags;

use Nextras\Orm\Entity\Entity;
use App\Models\Orm\TagsToObjects\TagToObjectEntity;

/**
 *

 * @property int $id {primary}
 * @property int $userId
 * @property string $name
 * @property string|NULL $color
 * @property TagToObjectEntity[]|NULL $objects {1:m TagToObjectEntity::$tagId, orderBy=id}
 */
class TagEntity extends Entity{
}